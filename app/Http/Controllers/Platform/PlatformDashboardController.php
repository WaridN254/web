<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformBusinessService;
use App\Services\PlatformSubscriptionService;
use App\Services\UsageService;
use App\Services\PlatformAuditService;
use Illuminate\Http\Request;

class PlatformDashboardController extends Controller
{
    public function __construct(
        private PlatformBusinessService $businessService,
        private PlatformSubscriptionService $subscriptionService,
        private UsageService $usageService,
        private PlatformAuditService $auditService,
    ) {}

    public function index()
    {
        $businessStats = $this->businessService->getBusinessStats();
        $subscriptionStats = $this->subscriptionService->getSubscriptionStats();
        $revenueStats = $this->subscriptionService->getRevenueStats();
        $globalUsage = $this->usageService->getGlobalUsage();
        $recentActivity = $this->auditService->getRecentActivity(10);

        return view('platform.dashboard', compact(
            'businessStats',
            'subscriptionStats',
            'revenueStats',
            'globalUsage',
            'recentActivity'
        ));
    }
}
