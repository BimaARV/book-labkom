<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\LabPc;
use Illuminate\Http\Request;

class LabMappingController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::withCount('labPcs')->get();
        return view('admin.lab_mappings.index', compact('laboratories'));
    }

    public function show(Laboratory $laboratory)
    {
        $pcs = $laboratory->labPcs()->with('latestDamage')->get();
        return view('admin.lab_mappings.show', compact('laboratory', 'pcs'));
    }

    public function updateConfig(Request $request, Laboratory $laboratory)
    {
        $request->validate([
            'grid_rows' => 'required|integer|min:1|max:50',
            'grid_cols' => 'required|integer|min:1|max:50',
            'grid_direction' => 'required|in:ltr,rtl',
        ]);

        $laboratory->update($request->only(['grid_rows', 'grid_cols', 'grid_direction']));

        return redirect()->route('admin.lab-mappings.show', $laboratory)->with('success', 'Konfigurasi ruangan berhasil disimpan.');
    }

    public function savePc(Request $request, Laboratory $laboratory)
    {
        $request->validate([
            'pc_id' => 'nullable|exists:lab_pcs,id',
            'grid_row' => 'required|integer',
            'grid_col' => 'required|integer',
            'name' => 'required|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'status' => 'required|in:active,maintenance,broken,inactive,kosong',
            'damage_description' => 'nullable|string',
        ]);

        $pc = LabPc::updateOrCreate(
            [
                'laboratory_id' => $laboratory->id,
                'grid_row' => $request->grid_row,
                'grid_col' => $request->grid_col,
            ],
            [
                'name' => $request->name,
                'ip_address' => $request->ip_address,
                'mac_address' => $request->mac_address,
                'status' => $request->status,
            ]
        );

        if (in_array($request->status, ['maintenance', 'broken', 'inactive'])) {
            // Check if there is an unresolved damage report
            $activeDamage = \App\Models\PcDamage::where('lab_pc_id', $pc->id)
                ->whereIn('status', ['reported', 'fixing'])
                ->first();
                
            $desc = $request->damage_description ?: 'Status diubah manual dari pengaturan denah.';
            if (!$activeDamage) {
                \App\Models\PcDamage::create([
                    'lab_pc_id' => $pc->id,
                    'description' => $desc,
                    'status' => 'reported',
                    'reported_at' => now(),
                ]);
            } else if ($request->filled('damage_description')) {
                $activeDamage->update(['description' => $desc]);
            }
        } else {
            // If changed to active, resolve any active damages
            \App\Models\PcDamage::where('lab_pc_id', $pc->id)
                ->whereIn('status', ['reported', 'fixing'])
                ->update([
                    'status' => 'fixed',
                    'resolved_at' => now()
                ]);
        }

        return response()->json(['success' => true, 'pc' => $pc]);
    }

    public function deletePc(LabPc $labPc)
    {
        $labPc->delete();
        return response()->json(['success' => true]);
    }
}
