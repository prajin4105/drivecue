<?php

namespace App\Filament\Widgets;

use App\Models\ReminderLog;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class WhatsAppLogsChart extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static ?string $chartId = 'whatsappLogsChart';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'WhatsApp Delivery Logs (Last 7 Days)';
    
    protected static ?int $sort = 1;

    /**
     * Chart options
     */
    protected function getOptions(): array
    {
        // Get data for last 7 days
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $logs = ReminderLog::where('message_type', 'whatsapp')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->get();

        $dates = [];
        $sentData = [];
        $failedData = [];
        
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays(6 - $i)->format('Y-m-d');
            $dates[] = Carbon::now()->subDays(6 - $i)->format('d M');
            $sentData[$date] = 0;
            $failedData[$date] = 0;
        }

        foreach ($logs as $log) {
            if ($log->status === 'sent') {
                $sentData[$log->date] = $log->count;
            } elseif ($log->status === 'failed') {
                $failedData[$log->date] = $log->count;
            }
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => [
                    'show' => false
                ]
            ],
            'series' => [
                [
                    'name' => 'Sent',
                    'data' => array_values($sentData),
                ],
                [
                    'name' => 'Failed',
                    'data' => array_values($failedData),
                ],
            ],
            'xaxis' => [
                'categories' => $dates,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#10b981', '#ef4444'],
            'stroke' => [
                'curve' => 'smooth',
            ],
        ];
    }
}
