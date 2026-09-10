<?php

namespace App\Http\Middleware;

use App\Services\BranchService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBranch
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && $user instanceof \App\Models\User) {
            $branchService = app(BranchService::class);
            $branchService->getActiveBranch();
        }

        return $next($request);
    }
}
