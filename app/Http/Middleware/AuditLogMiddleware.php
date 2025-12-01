<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonSerializable;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldAudit($request)) {
            $entity = $this->resolveEntity($request);
            $entityId = $this->resolveEntityId($request);
            AuditLogger::log(
                action: sprintf('http.%s', strtolower($request->method())),
                entity: $entity,
                entityId: $entityId,
                diff: [
                    'path' => $request->path(),
                    'status' => $response->status(),
                    'query' => $request->query(),
                ]
            );
        }

        return $response;
    }

    protected function shouldAudit(Request $request): bool
    {
        return Str::startsWith($request->path(), 'api/v1/assets')
            || Str::startsWith($request->path(), 'api/v1/reports');
    }

    protected function resolveEntity(Request $request): string
    {
        if (Str::startsWith($request->path(), 'api/v1/assets')) {
            return 'assets';
        }
        if (Str::startsWith($request->path(), 'api/v1/reports')) {
            return 'reports';
        }
        return 'http';
    }

    protected function resolveEntityId(Request $request): string
    {
        $route = $request->route();
        $parameters = $route?->parameters() ?? [];
        foreach (['asset', 'report', 'id'] as $key) {
            if (! empty($parameters[$key])) {
                return $this->extractParameterKey($parameters[$key]);
            }
        }

        return $this->extractParameterKey($parameters['asset_id'] ?? $parameters['report_id'] ?? 'n/a');
    }

    protected function extractParameterKey(mixed $parameter): string
    {
        if ($parameter instanceof Model) {
            return (string) $parameter->getKey();
        }

        if ($parameter instanceof Arrayable) {
            $parameter = $parameter->toArray();
        }

        if ($parameter instanceof JsonSerializable) {
            $parameter = $parameter->jsonSerialize();
        }

        if (is_array($parameter) && isset($parameter['id'])) {
            return (string) $parameter['id'];
        }

        if (is_string($parameter)) {
            $decoded = json_decode($parameter, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['id'])) {
                return (string) $decoded['id'];
            }

            return Str::limit($parameter, 255, '');
        }

        return Str::limit((string) $parameter, 255, '');
    }
}
