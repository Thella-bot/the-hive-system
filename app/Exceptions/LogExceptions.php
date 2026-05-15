<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;


class LogExceptions
{
    public function __invoke(Throwable $e): void
    {
        $request = request();

        Log::error('exception.reported', [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'route_name' => optional($request->route())->getName(),
            'user_id' => optional($request->user())->id,
            'input' => $request->except(['password', 'password_confirmation', 'current_password']),
        ]);
    }
}