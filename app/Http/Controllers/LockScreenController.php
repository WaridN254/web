<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LockScreenController extends Controller
{
    public function lock(Request $request)
    {
        session(['locked' => true]);

        return response()->json(['ok' => true]);
    }

    public function status(Request $request)
    {
        return response()->json(['locked' => (bool) session('locked', false)]);
    }

    public function unlock(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = $request->user();
        $valid = $user && Hash::check((string) $request->input('password'), $user->getAuthPassword());

        if ($valid) {
            session()->forget('locked');

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false, 'message' => 'Incorrect password. Please try again.'], 422);
    }
}