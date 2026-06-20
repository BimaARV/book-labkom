<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $acceptedBookings = Booking::where('status', 'accepted')->count();
        $rejectedBookings = Booking::where('status', 'rejected')->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();
        
        $recentBookings = Booking::with('laboratory', 'businessUnit', 'subBusinessUnit')->orderBy('created_at', 'desc')->take(5)->get();

        $businessUnitChartData = ['parentLabels' => [], 'parentData' => [], 'childrenMap' => []];
        $labChartData = ['labels' => [], 'data' => []];

        $validTotalBookings = Booking::whereNotIn('status', ['cancelled', 'rejected'])->count();
        
        if ($validTotalBookings > 0) {
            // --- Hierarchical Business Unit Stats ---
            $buStats = Booking::select('business_unit_id', 'sub_business_unit_id', \DB::raw('count(*) as total'))
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->with(['businessUnit', 'subBusinessUnit'])
                ->groupBy('business_unit_id', 'sub_business_unit_id')
                ->get();
                
            $parents = [];
            $children = [];
            
            foreach ($buStats as $stat) {
                if (!$stat->businessUnit) continue;
                $parentName = $stat->businessUnit->name;
                $subUnitName = optional($stat->subBusinessUnit)->name;
                
                if (!isset($parents[$parentName])) $parents[$parentName] = 0;
                $parents[$parentName] += $stat->total;
                
                $childName = $subUnitName ? "{$parentName} - {$subUnitName}" : "{$parentName} (Pusat/Induk)";
                if (!isset($children[$parentName])) $children[$parentName] = [];
                if (!isset($children[$parentName][$childName])) $children[$parentName][$childName] = 0;
                $children[$parentName][$childName] += $stat->total;
            }

            arsort($parents);
            
            foreach ($parents as $parentName => $parentTotal) {
                $businessUnitChartData['parentLabels'][] = $parentName;
                $businessUnitChartData['parentData'][] = $parentTotal;
                
                $parentIndex = count($businessUnitChartData['parentLabels']) - 1;
                $parentChildren = $children[$parentName];
                arsort($parentChildren);
                
                $childLabels = [];
                $childData = [];
                foreach ($parentChildren as $childName => $childTotal) {
                    $childLabels[] = $childName;
                    $childData[] = $childTotal;
                }
                
                $businessUnitChartData['childrenMap'][$parentIndex] = [
                    'labels' => $childLabels,
                    'data' => $childData,
                ];
            }

            // --- Laboratory Stats ---
            $lStats = Booking::select('laboratory_id', 'is_all_labs', \DB::raw('count(*) as total'))
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->with('laboratory')
                ->groupBy('laboratory_id', 'is_all_labs')
                ->get();
                
            $labArray = [];
            foreach ($lStats as $stat) {
                $name = $stat->is_all_labs ? 'Semua Labkom' : (optional($stat->laboratory)->name ?? 'Unknown');
                $labArray[] = ['name' => $name, 'total' => $stat->total];
            }
            usort($labArray, function($a, $b) { return $b['total'] <=> $a['total']; });
            
            $labChartData['labels'] = array_column($labArray, 'name');
            $labChartData['data'] = array_column($labArray, 'total');
        }

        return view('dashboard', compact(
            'totalBookings', 
            'pendingBookings', 
            'acceptedBookings', 
            'rejectedBookings',
            'completedBookings',
            'cancelledBookings',
            'recentBookings',
            'businessUnitChartData',
            'labChartData',
            'validTotalBookings'
        ));
    }
}
