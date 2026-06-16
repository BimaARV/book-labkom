<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabPc;
use App\Models\PcDamage;
use Illuminate\Http\Request;

class PcDamageController extends Controller
{
    public function index(Request $request)
    {
        $query = PcDamage::with('labPc.laboratory')
            ->whereIn('status', ['reported', 'fixing']);

        if ($request->filled('laboratory_id')) {
            $query->whereHas('labPc', function($q) use ($request) {
                $q->where('laboratory_id', $request->laboratory_id);
            });
        }

        $damages = $query->orderBy('created_at', 'desc')->get();
        
        $laboratories = \App\Models\Laboratory::orderBy('name')->get();
            
        return view('admin.pc_damages.index', compact('damages', 'laboratories'));
    }

    public function report(Request $request, LabPc $labPc)
    {
        $request->validate([
            'status' => 'required|in:maintenance,broken',
            'description' => 'required|string',
        ]);

        $labPc->update(['status' => $request->status]);

        $damage = PcDamage::create([
            'lab_pc_id' => $labPc->id,
            'description' => $request->description,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        return response()->json(['success' => true, 'pc' => $labPc, 'damage' => $damage]);
    }

    public function updateStatus(Request $request, PcDamage $pcDamage)
    {
        $request->validate([
            'status' => 'required|in:reported_broken,reported_inactive,reported_maintenance,fixing,fixed',
        ]);

        if ($request->status === 'reported_broken') {
            $pcDamage->update(['status' => 'reported']);
            $pcDamage->labPc()->update(['status' => 'broken']);
        } elseif ($request->status === 'reported_inactive') {
            $pcDamage->update(['status' => 'reported']);
            $pcDamage->labPc()->update(['status' => 'inactive']);
        } elseif ($request->status === 'reported_maintenance') {
            $pcDamage->update(['status' => 'reported']);
            $pcDamage->labPc()->update(['status' => 'maintenance']);
        } elseif ($request->status === 'fixing') {
            $pcDamage->update(['status' => 'fixing']);
        } elseif ($request->status === 'fixed') {
            $pcDamage->update([
                'status' => 'fixed',
                'resolved_at' => now(),
            ]);
            $pcDamage->labPc()->update(['status' => 'active']);
        }

        return redirect()->back()->with('success', 'Status kerusakan berhasil diperbarui.');
    }
}
