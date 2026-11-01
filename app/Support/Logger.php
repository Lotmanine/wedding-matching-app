<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Structured application logger. Writes to the `app_structured` channel if defined,
 * otherwise falls back to the default log channel. Every entry carries an event name,
 * user_id (if authenticated), environment, and a correlation id when available.
 */
class Logger
{
    public static function info(string $event, array $context = []): void
    {
        self::write('info', $event, $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        self::write('warning', $event, $context);
    }

    public static function error(string $event, array $context = []): void
    {
        self::write('error', $event, $context);
    }

    private static function write(string $level, string $event, array $context): void
    {
        $context = array_merge([
            'event'       => $event,
            'user_id'     => Auth::id(),
            'environment' => app()->environment(),
            'request_id'  => request()?->header('X-Request-Id') ?? request()?->header('X-Correlation-Id'),
        ], self::redact($context));

        $channel = config('logging.channels.app_structured') ? Log::channel('app_structured') : Log::channel(config('logging.default'));
        $channel->log($level, $event, $context);
    }

    private static function redact(array $context): array
    {
        $sensitive = ['password', 'password_confirmation', 'couple_whatsapp', 'whatsapp', 'phone', 'remember_token'];
        foreach ($sensitive as $key) {
            if (array_key_exists($key, $context)) {
                $context[$key] = '[REDACTED]';
            }
        }

        return $context;
    }
}
