<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueGrowthChart extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static ?string $chartId = 'revenueGrowthChart';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Revenue Growth (Last 6 Months) (INR)';
    
    protected static ?int $sort = 2;

    /**
     * Chart options
     */
    protected function getOptions(): array
    {
        $months = [];
        $revenueData = [];

        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $startOfMonth = Carbon::now()->subMonths($i)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonths($i)->endOfMonth();
            
            $monthName = $startOfMonth->format('M Y');
            $months[] = $monthName;
            
            $revenue = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');
                
            $revenueData[] = (float) ($revenue / 100);
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'toolbar' => [
                    'show' => false
                ]
            ],
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $revenueData,
                ],
            ],
            'xaxis' => [
                'categories' => $months,
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
            'colors' => ['#3b82f6'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                ],
            ],
        ];
    }
}
