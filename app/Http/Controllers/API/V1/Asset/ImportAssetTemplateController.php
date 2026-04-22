<?php

namespace App\Http\Controllers\API\V1\Asset;

use App\Exports\AssetTemplateExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ImportAssetTemplateController extends Controller
{
    public function __invoke()
    {
        return Excel::download(new AssetTemplateExport(), 'template-import-aset.xlsx');
    }
}

