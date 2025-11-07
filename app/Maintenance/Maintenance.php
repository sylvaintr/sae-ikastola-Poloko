<?php

namespace App\Maintenance;

/**
 * Optional application-level maintenance handler.
 *
 * If you prefer to manage maintenance mode via a class rather than the
 * generated `storage/framework/maintenance.php` file, implement the
 * static `handle()` method here.
 */
class Maintenance
{
    /**
     * Handle an application maintenance state.
     *
     * The default behavior is to include the generated maintenance file if it
     * exists, otherwise return a simple 503 response. You may replace this
     * logic with custom rendering or routing as needed.
     */
    public static function handle(): void
    {
        $legacy = __DIR__ . '/../../storage/framework/maintenance.php';

        if (file_exists($legacy)) {
            require $legacy;
            return;
        }

        // Minimal fallback if no maintenance stub exists.
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/plain; charset=utf-8');
        }

        echo 'The application is currently down for maintenance.';
        exit(0);
    }
}
