<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformAuditService;
use Illuminate\Http\Request;

class PlatformAuditLogController extends Controller
{
    public function __construct(
        private PlatformAuditService $auditService,
    ) {}

    public function index(Request $request)
    {
        $logs = $this->auditService->getLogs($request->only([
            'action', 'tenant_id', 'platform_user_id', 'resource_type',
            'search', 'date_from', 'date_to'
        ]));

        return view('platform.audit-logs.index', compact('logs'));
    }
}
