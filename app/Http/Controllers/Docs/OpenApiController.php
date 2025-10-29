<?php

namespace App\Http\Controllers\Docs;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Controller;

class OpenApiController extends Controller
{
    public function ui()
    {
        return view('swagger');
    }

    public function spec(Filesystem $fs)
    {
        $path = base_path('docs/openapi.yaml');
        if (! $fs->exists($path)) {
            abort(404, 'OpenAPI spec not found');
        }
        return response($fs->get($path), 200, [
            'Content-Type' => 'application/yaml',
        ]);
    }
}

