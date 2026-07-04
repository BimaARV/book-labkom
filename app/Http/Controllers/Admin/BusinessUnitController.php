<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessUnit;

class BusinessUnitController extends Controller
{
    public function index()
    {
        $businessUnits = BusinessUnit::with('subUnits')->get();
        return view('admin.business_units.index', compact('businessUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:business_units',
            'name' => 'required|string|max:255',
        ]);

        $businessUnit = BusinessUnit::create($request->all());
        
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Unit Bisnis', 'ditambahkan', $businessUnit->name, auth()->user());

        return back()->with('success', 'Unit Bisnis berhasil ditambahkan.');
    }

    public function update(Request $request, BusinessUnit $businessUnit)
    {
        $request->validate([
            'code' => 'required|string|unique:business_units,code,' . $businessUnit->id,
            'name' => 'required|string|max:255',
        ]);

        $businessUnit->update($request->all());

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Unit Bisnis', 'diperbarui', $businessUnit->name, auth()->user());

        return back()->with('success', 'Unit Bisnis berhasil diperbarui.');
    }

    public function destroy(BusinessUnit $businessUnit)
    {
        $unitName = $businessUnit->name;
        $businessUnit->delete();

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Unit Bisnis', 'dihapus', $unitName, auth()->user());

        return back()->with('success', 'Unit Bisnis berhasil dihapus.');
    }

    public function storeSubUnit(Request $request, BusinessUnit $businessUnit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subUnit = $businessUnit->subUnits()->create([
            'name' => $request->name
        ]);

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Fakultas / Sub Unit', 'ditambahkan', $subUnit->name, auth()->user());

        return back()->with('success', 'Sub Unit berhasil ditambahkan.');
    }

    public function updateSubUnit(Request $request, \App\Models\SubBusinessUnit $subUnit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subUnit->update(['name' => $request->name]);

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Fakultas / Sub Unit', 'diperbarui', $subUnit->name, auth()->user());

        return back()->with('success', 'Sub Unit berhasil diperbarui.');
    }

    public function destroySubUnit(\App\Models\SubBusinessUnit $subUnit)
    {
        $subUnitName = $subUnit->name;
        $subUnit->delete();

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Fakultas / Sub Unit', 'dihapus', $subUnitName, auth()->user());

        return back()->with('success', 'Sub Unit berhasil dihapus.');
    }
}
