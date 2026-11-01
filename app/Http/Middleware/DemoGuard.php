<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The demo logins are published on the login pages. This keeps their sessions
 * away from what could lock other visitors out or expose an integration.
 */
class DemoGuard
{
    /** Pages hidden from demo accounts: the webhook forms display signing secrets. */
    private const HIDDEN = ['admin.webhooks.*'];

    /** The back-office actions a demo admin may perform; every other one is refused. */
    private const ADMIN_WRITES = [
        'admin.logout', 'admin.providers.*', 'admin.requests.*', 'admin.service_requests.*',
        'admin.purchases.*', 'admin.disputes.*', 'admin.feedback.*', 'admin.packs.*',
    ];

    /** Account credentials, outside the back-office. */
    private const CREDENTIALS = ['password.update', 'password.confirm'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('wedding.demo.enabled') || ! $this->isDemoAccount($request)) {
            return $next($request);
        }

        $message = __('This action is disabled in the public demo.');

        if ($request->routeIs(...self::HIDDEN)) {
            return redirect()->route('admin.dashboard')->with('error', $message);
        }

        if (! $request->isMethodSafe() && $this->isForbiddenWrite($request)) {
            return back()->with('error', $message);
        }

        return $next($request);
    }

    private function isForbiddenWrite(Request $request): bool
    {
        if ($request->routeIs('admin.*')) {
            return ! $request->routeIs(...self::ADMIN_WRITES);
        }

        return $request->routeIs(...self::CREDENTIALS);
    }

    private function isDemoAccount(Request $request): bool
    {
        $email = $request->user()?->email;

        return $email !== null
            && in_array($email, array_column(config('wedding.demo.accounts'), 'email'), true);
    }
}
