<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Get lang from 'Accept-Language' header (e.g., 'en' or 'fa')
        $locale = $request->header('Accept-Language');

        // Set a default locale if the header is missing or unsupported
        if (!in_array($locale, ['en', 'fa'])) {
            $locale = 'fa'; // Or your default
        }

        // Set the application locale for this request
        App::setLocale($locale);

        return $next($request);
    }
}
