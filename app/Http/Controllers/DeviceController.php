<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     */
    public function index()
    {
        $devices = Device::with(['monitorings' => function ($query) {
            $query->latest('recorded_at')->limit(1);
        }])->paginate(10);

        return view('device.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device.
     */
    public function create()
    {
        return view('device.create');
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Generate unique device ID
        $deviceId = 'DEVICE_' . Str::upper(Str::random(10)) . '_' . time();

        Device::create([
            'device_name' => $request->device_name,
            'location' => $request->location,
            'device_id' => $deviceId,
        ]);

        return redirect()->route('device.index')->with('success', 'Device berhasil ditambahkan. Device ID: ' . $deviceId);
    }

    /**
     * Show the form for editing the specified device.
     */
    public function edit(Device $device)
    {
        return view('device.edit', compact('device'));
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $request, Device $device)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $device->update([
            'device_name' => $request->device_name,
            'location' => $request->location,
        ]);

        return redirect()->route('device.index')->with('success', 'Device berhasil diperbarui.');
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(Device $device)
    {
        $device->delete();

        return back()->with('success', 'Device berhasil dihapus.');
    }
}
