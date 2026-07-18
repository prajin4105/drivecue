<?php

namespace App\Http\Controllers;

use App\Models\VehicleRecord;
use App\Models\User;
use App\Models\ReminderLog;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    private function checkPlanAccess()
    {
        $userId = auth()->id();
        $sub = Subscription::where('user_id', $userId)->where('status', 'active')->orderBy('end_date', 'desc')->first();
        
        if ($sub && Carbon::today()->lessThanOrEqualTo(Carbon::parse($sub->end_date))) {
            return true;
        }

        $lastSub = Subscription::where('user_id', $userId)->orderBy('end_date', 'desc')->first();
        if ($lastSub) {
            $graceEnd = Carbon::parse($lastSub->end_date)->addDays(7);
            if (Carbon::today()->lessThanOrEqualTo($graceEnd)) {
                return true; // Inside grace period
            }
        }
        
        return false;
    }
    private function normalizeMobile(string $mobile): string
    {
        $clean = preg_replace('/\D/', '', $mobile) ?? '';
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            return substr($clean, 2);
        }
        return $clean;
    }

    private function isValidMobile(string $mobile): bool
    {
        return preg_match('/^[0-9]{10}$/', $mobile) === 1;
    }

    private function normalizeVehicleNumber(string $number): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $number) ?? '');
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $q = trim($request->input('q') ?? '');
        $status = $request->input('status');

        $today = Carbon::today()->toDateString();
        $sevenDays = Carbon::today()->addDays(7)->toDateString();

        // Base query for counts
        $baseQuery = VehicleRecord::where('user_id', $userId);

        $totalAll = (clone $baseQuery)->count();
        $totalExpired = (clone $baseQuery)->where('expiry_date', '<', $today)->count();
        $totalExpiring = (clone $baseQuery)->whereBetween('expiry_date', [$today, $sevenDays])->count();
        $totalActive = (clone $baseQuery)->where('expiry_date', '>', $sevenDays)->count();

        // Main filter query
        $query = VehicleRecord::where('user_id', $userId);

        if ($q !== '') {
            $normalizedVehicle = $this->normalizeVehicleNumber($q);
            $query->where(function ($sub) use ($q, $normalizedVehicle) {
                $sub->where('customer_name', 'like', '%' . $q . '%')
                    ->orWhere('customer_mobile', 'like', '%' . $q . '%')
                    ->orWhere('vehicle_number', 'like', '%' . $normalizedVehicle . '%');
            });
        }

        if ($status === 'expiring') {
            $query->whereBetween('expiry_date', [$today, $sevenDays]);
        } elseif ($status === 'expired') {
            $query->where('expiry_date', '<', $today);
        } elseif ($status === 'active') {
            $query->where('expiry_date', '>', $sevenDays);
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 75, 100])) {
            $perPage = 25;
        }

        $records = $query->orderBy('expiry_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $sentTodayIds = ReminderLog::where('user_id', $userId)
            ->where('message_type', 'whatsapp')
            ->where('status', 'sent')
            ->whereDate('created_at', Carbon::today())
            ->pluck('vehicle_record_id')
            ->toArray();

        return view('customers.index', compact(
            'records', 'totalAll', 'totalActive', 'totalExpiring', 'totalExpired', 'q', 'status', 'sentTodayIds'
        ));
    }

    public function create()
    {
        $record = new VehicleRecord();
        $record->issue_date = Carbon::today();
        $record->expiry_date = Carbon::today()->addYear();
        $record->puc_price = 0.00;

        return view('customers.form', [
            'record' => $record,
            'isEditing' => false
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->checkPlanAccess()) {
            return redirect()->route('pricing.index')->with('error', 'Your subscription has ended and the 7-day grace period is over. Please renew your plan to add vehicles.');
        }

        $userId = auth()->id();

        // Normalize first so validation rules check clean data
        $request->merge([
            'customer_mobile' => $this->normalizeMobile($request->input('customer_mobile') ?? ''),
            'vehicle_number' => $this->normalizeVehicleNumber($request->input('vehicle_number') ?? ''),
        ]);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_mobile' => 'required|regex:/^[0-9]{10}$/',
            'vehicle_number' => 'required|alpha_num|max:30',
            'vehicle_type' => 'required|in:Bike,Car,Auto,Truck,Bus,Other',
            'fuel_type' => 'required|in:Petrol,Diesel,CNG,LPG,Hybrid',
            'puc_certificate_number' => 'nullable|string|max:100',
            'puc_price' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Customer name is required.',
            'customer_mobile.required' => 'Mobile number is required.',
            'customer_mobile.regex' => 'Please enter a valid 10-digit mobile number.',
            'vehicle_number.required' => 'Vehicle number is required.',
            'vehicle_number.alpha_num' => 'Vehicle number must be alphanumeric.',
            'fuel_type.required' => 'Fuel type is required.',
            'fuel_type.in' => 'Electric vehicles do not require PUC. Please choose a different fuel type.',
            'puc_price.required' => 'PUC price is required.',
            'puc_price.min' => 'PUC price cannot be negative.',
            'issue_date.required' => 'Issue date is required.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.after_or_equal' => 'Expiry date cannot be before issue date.',
        ]);

        // Check duplicate
        $existing = VehicleRecord::where('user_id', $userId)
            ->where('vehicle_number', $validated['vehicle_number'])
            ->first();

        if ($existing) {
            return redirect()->route('customers.edit', $existing->id)
                ->with('warning', 'This vehicle already exists. Existing record opened for update.');
        }

        VehicleRecord::create([
            'user_id' => $userId,
            'customer_name' => trim($validated['customer_name']),
            'customer_mobile' => $validated['customer_mobile'],
            'vehicle_number' => $validated['vehicle_number'],
            'vehicle_type' => $validated['vehicle_type'],
            'fuel_type' => $validated['fuel_type'],
            'puc_certificate_number' => trim($validated['puc_certificate_number'] ?? '') ?: null,
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'puc_price' => (float)$validated['puc_price'],
            'notes' => trim($validated['notes'] ?? '') ?: null,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer/vehicle record added successfully.');
    }

    public function edit($id)
    {
        $record = VehicleRecord::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view('customers.form', [
            'record' => $record,
            'isEditing' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = auth()->id();
        $record = VehicleRecord::where('id', $id)->where('user_id', $userId)->firstOrFail();

        // Normalize first so validation rules check clean data
        $request->merge([
            'customer_mobile' => $this->normalizeMobile($request->input('customer_mobile') ?? ''),
            'vehicle_number' => $this->normalizeVehicleNumber($request->input('vehicle_number') ?? ''),
        ]);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_mobile' => 'required|regex:/^[0-9]{10}$/',
            'vehicle_number' => 'required|alpha_num|max:30',
            'vehicle_type' => 'required|in:Bike,Car,Auto,Truck,Bus,Other',
            'fuel_type' => 'required|in:Petrol,Diesel,CNG,LPG,Hybrid',
            'puc_certificate_number' => 'nullable|string|max:100',
            'puc_price' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Customer name is required.',
            'customer_mobile.required' => 'Mobile number is required.',
            'customer_mobile.regex' => 'Please enter a valid 10-digit mobile number.',
            'vehicle_number.required' => 'Vehicle number is required.',
            'vehicle_number.alpha_num' => 'Vehicle number must be alphanumeric.',
            'fuel_type.required' => 'Fuel type is required.',
            'fuel_type.in' => 'Electric vehicles do not require PUC. Please choose a different fuel type.',
            'puc_price.required' => 'PUC price is required.',
            'puc_price.min' => 'PUC price cannot be negative.',
            'issue_date.required' => 'Issue date is required.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.after_or_equal' => 'Expiry date cannot be before issue date.',
        ]);

        // Check duplicate excluding this ID
        $existing = VehicleRecord::where('user_id', $userId)
            ->where('vehicle_number', $validated['vehicle_number'])
            ->where('id', '<>', $id)
            ->first();

        if ($existing) {
            return redirect()->route('customers.edit', $existing->id)
                ->with('warning', 'This vehicle already exists. Existing record opened for update.');
        }

        $record->update([
            'customer_name' => trim($validated['customer_name']),
            'customer_mobile' => $validated['customer_mobile'],
            'vehicle_number' => $validated['vehicle_number'],
            'vehicle_type' => $validated['vehicle_type'],
            'fuel_type' => $validated['fuel_type'],
            'puc_certificate_number' => trim($validated['puc_certificate_number'] ?? '') ?: null,
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'puc_price' => (float)$validated['puc_price'],
            'notes' => trim($validated['notes'] ?? '') ?: null,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $record = VehicleRecord::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $record->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Record deleted successfully.');
    }

    public function import(Request $request)
    {
        if (!$this->checkPlanAccess()) {
            return response()->json(['success' => false, 'message' => 'Your subscription has ended and the 7-day grace period is over. Please renew your plan to add vehicles.']);
        }

        $userId = auth()->id();

        if (!$request->hasFile('csv_file')) {
            return response()->json(['success' => false, 'message' => 'No file uploaded. Please select a CSV file.']);
        }

        $file = $request->file('csv_file');
        $fileName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext !== 'csv' && $ext !== 'txt') {
            return response()->json(['success' => false, 'message' => 'Invalid file type. Please upload a standard CSV file (.csv).']);
        }

        $filePath = $file->getRealPath();
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return response()->json(['success' => false, 'message' => 'Could not read uploaded file.']);
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 4) {
            fclose($handle);
            return response()->json(['success' => false, 'message' => 'Invalid CSV structure. The CSV must contain at least headers for Name, Mobile, Vehicle Number, and Dates.']);
        }

        $inserted = 0;
        $updated = 0;
        $unchanged = 0;
        $skippedCount = 0;
        $errors = [];
        $lineNumber = 1;

        $validVehicleTypes = ['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other'];
        $validFuelTypes = ['Petrol', 'Diesel', 'CNG', 'LPG', 'Electric', 'Hybrid'];

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            if (count($row) < 9) {
                $row = array_pad($row, 9, '');
            }

            $cName = trim((string)($row[0] ?? ''));
            $cMobile = $this->normalizeMobile((string)($row[1] ?? ''));
            $vNumber = $this->normalizeVehicleNumber((string)($row[2] ?? ''));
            $vType = trim((string)($row[3] ?? ''));
            $fType = trim((string)($row[4] ?? ''));
            $pucCert = trim((string)($row[5] ?? ''));
            $issueDateStr = trim((string)($row[6] ?? ''));
            $expiryDateStr = trim((string)($row[7] ?? ''));
            $notes = trim((string)($row[8] ?? ''));

            if ($vNumber === 'MH12AB1234' || $cMobile === '9876543210') {
                continue;
            }

            $lineErrors = [];

            if ($vNumber === '') {
                $lineErrors[] = 'Missing vehicle number.';
            }
            if (!$this->isValidMobile($cMobile)) {
                $lineErrors[] = 'Invalid or missing mobile number (must be 10 digits).';
            }

            $parsedIssue = $this->parseImportDate($issueDateStr);
            $parsedExpiry = $this->parseImportDate($expiryDateStr);

            if (!$parsedIssue) {
                $lineErrors[] = 'Invalid issue date format (use YYYY-MM-DD).';
            }
            if (!$parsedExpiry) {
                $lineErrors[] = 'Invalid expiry date format (use YYYY-MM-DD).';
            }

            $matchedVType = 'Bike';
            foreach ($validVehicleTypes as $t) {
                if (strcasecmp($t, $vType) === 0) {
                    $matchedVType = $t;
                    break;
                }
            }

            $matchedFType = null;
            foreach ($validFuelTypes as $f) {
                if (strcasecmp($f, $fType) === 0) {
                    $matchedFType = $f;
                    break;
                }
            }

            if ($lineErrors) {
                $skippedCount++;
                $errors[] = 'Row ' . $lineNumber . ': ' . implode(' ', $lineErrors);
                continue;
            }

            try {
                $record = VehicleRecord::where('user_id', $userId)
                    ->where('vehicle_number', $vNumber)
                    ->first();

                if ($record) {
                    $hasChanges = $record->customer_name !== $cName ||
                                  $record->customer_mobile !== $cMobile ||
                                  $record->vehicle_type !== $matchedVType ||
                                  $record->fuel_type !== $matchedFType ||
                                  $record->puc_certificate_number !== $pucCert ||
                                  $record->issue_date->toDateString() !== $parsedIssue ||
                                  $record->expiry_date->toDateString() !== $parsedExpiry ||
                                  $record->notes !== $notes;

                    if ($hasChanges) {
                        $record->update([
                            'customer_name' => $cName ?: null,
                            'customer_mobile' => $cMobile,
                            'vehicle_type' => $matchedVType,
                            'fuel_type' => $matchedFType,
                            'puc_certificate_number' => $pucCert ?: null,
                            'issue_date' => $parsedIssue,
                            'expiry_date' => $parsedExpiry,
                            'notes' => $notes ?: null,
                        ]);
                        $updated++;
                    } else {
                        $unchanged++;
                    }
                } else {
                    VehicleRecord::create([
                        'user_id' => $userId,
                        'customer_name' => $cName ?: null,
                        'customer_mobile' => $cMobile,
                        'vehicle_number' => $vNumber,
                        'vehicle_type' => $matchedVType,
                        'fuel_type' => $matchedFType,
                        'puc_certificate_number' => $pucCert ?: null,
                        'issue_date' => $parsedIssue,
                        'expiry_date' => $parsedExpiry,
                        'notes' => $notes ?: null,
                        'puc_price' => 0.00,
                    ]);
                    $inserted++;
                }
            } catch (\Throwable $e) {
                $skippedCount++;
                $errors[] = 'Row ' . $lineNumber . ' (DB Error): ' . $e->getMessage();
            }
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'inserted' => $inserted,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'skipped' => $skippedCount,
            'errors' => $errors,
            'message' => "Import completed: {$inserted} added, {$updated} updated, {$unchanged} unchanged, {$skippedCount} skipped."
        ]);
    }

    private function parseImportDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);
        if ($dateStr === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'j/n/Y', 'j-n-Y', 'd.m.Y', 'Y.m.d'];
        foreach ($formats as $fmt) {
            try {
                $d = \DateTime::createFromFormat($fmt, $dateStr);
                if ($d && $d->format($fmt) === $dateStr) {
                    return $d->format('Y-m-d');
                }
            } catch (\Throwable $e) {}
        }

        $ts = strtotime($dateStr);
        if ($ts !== false && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return null;
    }
}
