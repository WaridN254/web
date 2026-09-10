<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PlatformSettingController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        $emailSettings = $settings->pull('email', collect());

        $currentMailer = config('mail.default');
        $mailerStatus = $currentMailer === 'log' ? 'development' : 'production';
        $queueDriver = config('queue.default');

        return view('platform.settings.index', compact('settings', 'emailSettings', 'mailerStatus', 'currentMailer', 'queueDriver'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = PlatformSetting::where('key', $key)->first();
            if ($setting && $setting->type === 'password' && empty($value)) {
                continue;
            }
            PlatformSetting::set($key, $value);
        }

        PlatformAuditLog::log('platform_settings_updated', null, null, 'PlatformSetting', null, 'Platform settings updated');

        return redirect()->route('platform.settings.index')
            ->with('success', 'Settings updated successfully. Mail transport changes take effect after queue worker restart.');
    }

    public function restartQueue()
    {
        Artisan::call('queue:restart');

        PlatformAuditLog::log('queue_restarted', null, null, 'PlatformSetting', null, 'Queue worker restarted via platform settings');

        return redirect()->route('platform.settings.index')
            ->with('success', 'Queue worker restart signal sent. Workers will restart on their next job processing cycle.');
    }
}
