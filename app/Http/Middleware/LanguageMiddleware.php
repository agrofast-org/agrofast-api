<?php

namespace App\Http\Middleware;

use App\Models\Hr\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $language = $request->header('Accept-Language') ?: $request->input('language');

        if ($language) {
            $locale = str_replace('-', '_', $language);
        } else {
            try {
                $user = User::auth();
                $locale = str_replace('-', '_', $user->language);
            } catch (\Exception $e) {
                $locale = str_replace('-', '_', env('APP_FALLBACK_LOCALE', 'en'));
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
