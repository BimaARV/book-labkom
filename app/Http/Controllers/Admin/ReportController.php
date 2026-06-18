<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function downloadPdf(Request $request)
    {
        $range = $request->get('range', 'this_week');
        $startDate = null;
        $endDate = null;
        $title = "Laporan Peminjaman Labkom";

        if ($range === 'this_week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
            $title .= " (" . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y') . ")";
        } elseif ($range === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $months = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
            $monthName = $months[$startDate->format('F')];
            $title .= " - " . $monthName . " " . $startDate->format('Y');
        } elseif ($range === 'custom') {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $title .= " (" . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y') . ")";
        } else {
            return redirect()->back()->with('error', 'Rentang waktu tidak valid.');
        }

        // Fetch bookings within range
        $bookings = Booking::with(['laboratory', 'businessUnit', 'subBusinessUnit'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate statistics
        $stats = [
            'total' => $bookings->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'accepted' => $bookings->where('status', 'accepted')->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'rejected' => $bookings->where('status', 'rejected')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        // Most active Business Unit & Labkom
        $parents = [];
        $children = [];
        $labkoms = [];

        foreach ($bookings as $booking) {
            if (!in_array($booking->status, ['rejected', 'cancelled'])) {
                if ($booking->laboratory || $booking->is_all_labs) {
                    $labName = $booking->is_all_labs ? 'Semua Labkom' : $booking->laboratory->name;
                    if (!isset($labkoms[$labName])) $labkoms[$labName] = 0;
                    $labkoms[$labName]++;
                }

                if ($booking->businessUnit) {
                    $parentName = $booking->businessUnit->name;
                    $subUnitName = optional($booking->subBusinessUnit)->name;
                    
                    if (!isset($parents[$parentName])) $parents[$parentName] = 0;
                    $parents[$parentName]++;
                    
                    $childName = $subUnitName ? "{$parentName} - {$subUnitName}" : "{$parentName} (Pusat/Induk)";
                    if (!isset($children[$parentName])) $children[$parentName] = [];
                    if (!isset($children[$parentName][$childName])) $children[$parentName][$childName] = 0;
                    $children[$parentName][$childName]++;
                }
            }
        }

        arsort($parents);
        arsort($labkoms);

        $parentLabels = [];
        $parentData = [];
        $childLabels = [];
        $childData = [];
        
        foreach ($parents as $parentName => $parentTotal) {
            $parentLabels[] = $parentName;
            $parentData[] = $parentTotal;
            
            $parentChildren = $children[$parentName];
            arsort($parentChildren);
            foreach ($parentChildren as $childName => $childTotal) {
                $childLabels[] = $childName;
                $childData[] = $childTotal;
            }
        }

        // Cari yang paling aktif spesifik (child/parent yang paling banyak peminjamannya)
        $mostActiveUnitName = '-';
        $mostActiveUnitCount = 0;
        foreach ($children as $parentName => $parentChildren) {
            foreach ($parentChildren as $childName => $childTotal) {
                if ($childTotal > $mostActiveUnitCount) {
                    $mostActiveUnitCount = $childTotal;
                    $mostActiveUnitName = $childName;
                }
            }
        }

        $mostActiveUnit = $mostActiveUnitCount > 0 ? $mostActiveUnitName . ' (' . $mostActiveUnitCount . ' peminjaman)' : '-';
        $mostPopularLab = !empty($labkoms) ? key($labkoms) . ' (' . current($labkoms) . ' peminjaman)' : '-';

        $baseColors = [
            '#002B5C', '#1E88E5', '#43A047', '#FDD835', 
            '#E53935', '#8E24AA', '#00ACC1', '#FB8C00',
            '#3949AB', '#00897B', '#7CB342', '#F4511E'
        ];

        // Generate Charts via QuickChart API
        $buChartBase64 = null;
        if (!empty($childData)) {
            $buColors = array_values(array_slice($baseColors, 0, count($childData)));
            
            $buTotal = array_sum($childData);
            $buPercentages = array_values(array_map(function($val) use ($buTotal) { return round(($val / $buTotal) * 100); }, $childData));

            $buLabelsWithCounts = [];
            foreach ($childLabels as $idx => $lbl) {
                $buLabelsWithCounts[] = $lbl . ' (' . $buPercentages[$idx] . '%, ' . $childData[$idx] . ' Peminjaman)';
            }

            $buChartConfig = [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $buLabelsWithCounts,
                    'datasets' => [
                        [
                            'data' => $buPercentages,
                            'backgroundColor' => $buColors,
                        ]
                    ]
                ],
                'options' => [
                    'legend' => [ 'position' => 'bottom' ],
                    'plugins' => [
                        'datalabels' => [
                            'color' => '#fff',
                            'font' => ['weight' => 'bold', 'size' => 14],
                            'formatter' => "function(value) { return value + '%'; }"
                        ]
                    ]
                ]
            ];
            $buChartUrl = 'https://quickchart.io/chart?w=600&h=450&c=' . urlencode(json_encode($buChartConfig));
            try {
                $buChartBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($buChartUrl));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('QuickChart BU Error: ' . $e->getMessage() . ' URL: ' . $buChartUrl);
            }
        }

        $labChartBase64 = null;
        if (!empty($labkoms)) {
            $labColors = array_values(array_slice($baseColors, 0, count($labkoms)));
            
            $labDataRaw = array_values($labkoms);
            $labTotal = array_sum($labDataRaw);
            $labPercentages = array_values(array_map(function($val) use ($labTotal) { return round(($val / $labTotal) * 100); }, $labDataRaw));

            $labLabelsRaw = array_keys($labkoms);
            $labLabelsWithCounts = [];
            foreach ($labLabelsRaw as $idx => $lbl) {
                $labLabelsWithCounts[] = $lbl . ' (' . $labPercentages[$idx] . '%, ' . $labDataRaw[$idx] . ' Peminjaman)';
            }

            $labChartConfig = [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $labLabelsWithCounts,
                    'datasets' => [[
                        'data' => $labPercentages,
                        'backgroundColor' => $labColors
                    ]]
                ],
                'options' => [
                    'legend' => [ 'position' => 'bottom' ],
                    'plugins' => [
                        'datalabels' => [
                            'color' => '#fff',
                            'font' => ['weight' => 'bold', 'size' => 14],
                            'formatter' => "function(value) { return value + '%'; }"
                        ]
                    ]
                ]
            ];
            $labChartUrl = 'https://quickchart.io/chart?w=600&h=450&c=' . urlencode(json_encode($labChartConfig));
            try {
                $labChartBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($labChartUrl));
            } catch (\Exception $e) {
                // Ignore error
            }
        }

        $data = compact('bookings', 'stats', 'mostActiveUnit', 'mostPopularLab', 'title', 'startDate', 'endDate', 'buChartBase64', 'labChartBase64');

        // Render PDF
        $pdf = Pdf::loadView('admin.reports.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Labkom_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }
}
