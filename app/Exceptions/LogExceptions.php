<?php
declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;


class LogExceptions
{
    private array $sensitiveFields = [
        'password', 'password_confirmation', 'current_password', 'token',
        'api_key', 'secret', 'credit_card_number', 'ssn', 'id_number',
        'csrf_token', 'remember_token', 'two_factor_secret',
    ];

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
            'input' => $this->sanitizeInput($request),
        ]);
    }

    private function sanitizeInput(Request $request): array
    {
        $input = $request->except($this->sensitiveFields);

        return $this->sanitizeArray($input);
    }

    private function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->sensitiveFields, true)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value) && strlen($value) > 200) {
                $sanitized[$key] = '[TRUNCATED] ' . substr($value, 0, 200);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}