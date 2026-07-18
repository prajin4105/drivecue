<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReminderLog;
use Carbon\Carbon;

class WhatsappLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ReminderLog::where('user_id', auth()->id())->with('vehicleRecord');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('customer_mobile', 'like', "%{$search}%")
                  ->orWhereHas('vehicleRecord', function($vq) use ($search) {
                      $vq->where('vehicle_number', 'like', "%{$search}%")
                         ->orWhere('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $from = $request->input('from') ? Carbon::parse($request->input('from')) : Carbon::today()->subDays(30);
        $to = $request->input('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        if ($request->filled('from') || $request->filled('to') || !$request->filled('search')) {
            $query->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 75, 100])) {
            $perPage = 25;
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('logs.whatsapp', compact('logs', 'from', 'to'));
    }
}
