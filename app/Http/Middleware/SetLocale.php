<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's language (French or Arabic) to the public site and
 * the provider area. The choice is made with the FR/AR switch in the header
 * and kept in a cookie. The back-office stays in French.
 */
class SetLocale
{
    public const COOKIE = 'zawaj_locale';

    public const SUPPORTED = ['fr', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(self::COOKIE);

        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true) || $request->is('admin', 'admin/*')) {
            $locale = 'fr';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
