<?php

namespace App\Http\Middleware;

use App\Services\LocalizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocalization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && $user instanceof \App\Models\User) {
            $service = app(LocalizationService::class);

            // Priority: session > user > tenant > app default
            $locale = Session::get('locale') ?? $service->resolveLanguage();
            $timezone = $service->resolveTimezone();

            App::setLocale($locale);
            config(['app.locale' => $locale]);
            config(['app.timezone' => $timezone]);

            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
