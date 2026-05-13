<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use App\Exports\AssetTemplateExport;
use Infra\Shared\Controllers\BaseController;
use Maatwebsite\Excel\Facades\Excel;

class ImportAssetTemplateController extends BaseController
{
    public function __invoke()
    {
        return Excel::download(new AssetTemplateExport(), 'template-import-aset.xlsx');
    }
}
