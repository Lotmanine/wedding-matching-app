<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's language (English or French). The choice is made with
 * the EN/FR switch in the header and kept in a cookie; without one, the
 * application locale applies.
 */
class SetLocale
{
    public const COOKIE = 'app_locale';

    public const SUPPORTED = ['en', 'fr'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(self::COOKIE);

        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
