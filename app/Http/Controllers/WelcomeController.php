<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $plans = Plan::active()->ordered()->get();

        return view('welcome', compact('plans'));
    }
}
