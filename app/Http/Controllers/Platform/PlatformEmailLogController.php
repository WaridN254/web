<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmailMail;

class PlatformEmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with('tenant')->latest();

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('recipient')) {
            $query->where('recipient', 'like', '%' . $request->recipient . '%');
        }

        if ($request->filled('days')) {
            $query->where('created_at', '>=', now()->subDays((int) $request->days));
        }

        $logs = $query->paginate(25)->withQueryString();

        $stats = [
            'total_today' => EmailLog::where('created_at', '>=', now()->startOfDay())->count(),
            'sent_today' => EmailLog::where('created_at', '>=', now()->startOfDay())->where('status', 'sent')->count(),
            'queued_today' => EmailLog::where('created_at', '>=', now()->startOfDay())->where('status', 'queued')->count(),
            'failed_today' => EmailLog::where('created_at', '>=', now()->startOfDay())->where('status', 'failed')->count(),
            'total_all_time' => EmailLog::count(),
            'failed_all_time' => EmailLog::where('status', 'failed')->count(),
        ];

        $types = EmailLog::distinct()->pluck('type')->filter()->values();

        $mailer = config('mail.default');
        $fromAddress = config('email.from.address');
        $queueConnection = config('queue.default');

        return view('platform.email-logs.index', compact(
            'logs', 'stats', 'types', 'mailer', 'fromAddress', 'queueConnection'
        ));
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Mail::to($request->email)->queue(new TestEmailMail());

            EmailLog::create([
                'recipient' => $request->email,
                'type' => 'test_email',
                'status' => 'queued',
            ]);

            return back()->with('success', "Test email queued for {$request->email}.");
        } catch (\Exception $e) {
            EmailLog::create([
                'recipient' => $request->email,
                'type' => 'test_email',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
