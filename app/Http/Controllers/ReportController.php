<?php

namespace App\Http\Controllers;

use App\Models\ReminderLog;
use App\Models\VehicleRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $from = $this->dateOrDefault($request->input('from'), Carbon::today()->startOfMonth());
        $to = $this->dateOrDefault($request->input('to'), Carbon::today()->endOfMonth());

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();
        $records = VehicleRecord::where('user_id', $userId)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $totalAdded = (clone $records)->count();
        $totalRevenue = (float) ((clone $records)->sum('puc_price') ?? 0);
        $expiringInRange = VehicleRecord::where('user_id', $userId)
            ->whereBetween('expiry_date', [$fromDate, $toDate])
            ->count();
        $expiredInRange = VehicleRecord::where('user_id', $userId)
            ->where('expiry_date', '<', Carbon::today()->toDateString())
            ->whereBetween('expiry_date', [$fromDate, $toDate])
            ->count();

        $messages = ReminderLog::where('user_id', $userId)
            ->where('message_type', 'whatsapp')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
        $messageTotal = (clone $messages)->count();
        $messageSent = (clone $messages)->where('status', 'sent')->count();
        $messageFailed = (clone $messages)->where('status', 'failed')->count();
        $messagePending = (clone $messages)->where('status', 'pending')->count();
        $deliveryRate = $messageTotal ? round($messageSent / $messageTotal * 100, 1) : 0;

        $types = ['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other'];
        $typeRows = (clone $records)->select('vehicle_type', DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(puc_price), 0) as revenue'))
            ->groupBy('vehicle_type')->get()->keyBy('vehicle_type');
        $vehicleSummary = collect($types)->map(fn ($type) => [
            'label' => $type,
            'total' => (int) ($typeRows[$type]->total ?? 0),
            'revenue' => (float) ($typeRows[$type]->revenue ?? 0),
        ]);
        $topType = $vehicleSummary->sortByDesc('total')->first();

        $period = CarbonPeriod::create($from, $to);
        $days = collect($period)->map(fn (Carbon $date) => $date->copy());
        $dailyRecords = (clone $records)->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->pluck('total', 'day');
        $dailyExpiring = VehicleRecord::where('user_id', $userId)
            ->whereBetween('expiry_date', [$fromDate, $toDate])
            ->selectRaw('expiry_date as day, COUNT(*) as total')->groupBy('expiry_date')->pluck('total', 'day');
        $chartDays = $days->count() > 31 ? $days->filter(fn (Carbon $date, int $index) => $index % (int) ceil($days->count() / 10) === 0)->values() : $days;
        $trendLabels = $chartDays->map(fn (Carbon $date) => $date->format('d M'))->all();
        $trendRecords = $chartDays->map(fn (Carbon $date) => (int) ($dailyRecords[$date->toDateString()] ?? 0))->all();
        $trendExpiring = $chartDays->map(fn (Carbon $date) => (int) ($dailyExpiring[$date->toDateString()] ?? 0))->all();

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 75, 100])) {
            $perPage = 25;
        }

        $followups = VehicleRecord::where('user_id', $userId)
            ->whereBetween('expiry_date', [$fromDate, $toDate])
            ->orderBy('expiry_date')->paginate($perPage)->withQueryString();

        $sentTodayIds = ReminderLog::where('user_id', $userId)
            ->where('message_type', 'whatsapp')
            ->where('status', 'sent')
            ->whereDate('created_at', Carbon::today())
            ->pluck('vehicle_record_id')
            ->toArray();

        return view('reports.index', compact(
            'from', 'to', 'totalAdded', 'totalRevenue', 'expiringInRange', 'expiredInRange',
            'messageTotal', 'messageSent', 'messageFailed', 'messagePending', 'deliveryRate',
            'vehicleSummary', 'topType', 'trendLabels', 'trendRecords', 'trendExpiring', 'followups', 'sentTodayIds'
        ));
    }

    private function dateOrDefault(?string $value, Carbon $default): Carbon
    {
        try {
            return filled($value) ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
