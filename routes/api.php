<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\ScanQueue;

/*
|--------------------------------------------------------------------------
| API Routes — Mobile Scanner Endpoint
|--------------------------------------------------------------------------
*/

Route::post('/scan', function (Request $request) {
    $validated = $request->validate([
        'barcode' => 'required|string|max:255',
    ]);

    $scan = ScanQueue::create([
        'barcode' => $validated['barcode'],
        'status' => 'pending',
        'ip_address' => $request->ip(),
    ]);

    return response()->json([
        'success' => true,
        'scan_id' => $scan->id,
        'message' => 'Barcode received',
    ]);
});

Route::get('/scan/pending', function (Request $request) {
    $scans = ScanQueue::where('status', 'pending')
        ->orderBy('created_at')
        ->get();

    return response()->json($scans);
});

Route::post('/scan/{scan}/processed', function (ScanQueue $scan) {
    $scan->update([
        'status' => 'processed',
        'processed_at' => now(),
    ]);

    return response()->json(['success' => true]);
});

Route::post('/scan/{scan}/failed', function (Request $request, ScanQueue $scan) {
    $scan->update([
        'status' => 'failed',
        'error_message' => $request->input('error', 'Unknown error'),
        'processed_at' => now(),
    ]);

    return response()->json(['success' => true]);
});
