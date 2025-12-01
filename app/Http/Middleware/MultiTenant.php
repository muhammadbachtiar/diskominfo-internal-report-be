<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Infra\Village\Models\Village;
use Symfony\Component\HttpFoundation\Response;

class MultiTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $villageId = $request->header('X-Village-ID');

        if (is_null($villageId)) {
            // Superadmin → akses schema global
            DB::statement('SET search_path TO public');
            return $next($request);
        }

        // Ambil schema dari table `villages`
        $schema = cache()->remember("village_schema:{$villageId}", 3600, function () use ($villageId) {
            return Village::find($villageId)?->schema_name;
        });

        if (! $schema) {
            abort(400, 'Village ID tidak valid atau schema tidak ditemukan.');
        }

        // Set schema PostgreSQL
        DB::statement("SET search_path TO {$schema}");

        return $next($request);
    }
}
