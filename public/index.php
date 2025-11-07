<?php

use Illuminate\Http\Request;
use App\Maintenance\Maintenance; // prefer a namespaced maintenance handler when available

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
// Prefer a namespaced class (imported via `use`) if your app provides one.
// Fall back to the legacy file-based maintenance stub when the class is not present.
if (class_exists(Maintenance::class)) {
    // If an application-defined Maintenance handler is available, call its static handler.
    // The handler should terminate or send a maintenance response as needed.
    Maintenance::handle();
} elseif (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    // Legacy fallback: include the generated maintenance file.
    require_once $maintenance;
}

// Register the Composer autoloader...
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
