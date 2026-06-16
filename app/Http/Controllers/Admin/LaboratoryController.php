<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laboratory;

class LaboratoryController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::withCount('bookings')->with('labPcs')->get();
        return view('admin.laboratories.index', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,maintenance'
        ]);

        $laboratory = Laboratory::create($request->all());
        
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Labkom', 'ditambahkan', $laboratory->name, auth()->user());

        return back()->with('success', 'Labkom berhasil ditambahkan.');
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,maintenance'
        ]);

        $laboratory->update($request->all());

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Labkom', 'diperbarui', $laboratory->name, auth()->user());

        return back()->with('success', 'Labkom berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory)
    {
        $labName = $laboratory->name;
        $laboratory->delete();

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendMasterDataNotification('Labkom', 'dihapus', $labName, auth()->user());

        return back()->with('success', 'Labkom berhasil dihapus.');
    }
}
