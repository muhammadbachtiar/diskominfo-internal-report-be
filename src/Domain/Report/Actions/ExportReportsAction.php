<?php

namespace Domain\Report\Actions;

use App\Exports\ReportExport;
use Illuminate\Support\Collection;
use Infra\Report\Models\Report;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;
use Maatwebsite\Excel\Facades\Excel;

class ExportReportsAction extends Action
{
    public function execute(string $format = 'csv'): array
    {
        CheckRolesAction::resolve()->execute('export-report');
        $rows = Report::query()->get(['number','title','status','created_at']);
        if ($format === 'xlsx') {
            $name = 'reports_'.date('Ymd_His').'.xlsx';
            $path = 'exports/'.$name;
            Excel::store(new ReportExport($rows), $path, 'local');
            $content = file_get_contents(storage_path('app/'.$path));
            return ['content' => $content, 'filename' => $name, 'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        }
        // CSV default
        $csv = "number,title,status,created_at\n";
        foreach ($rows as $r) {
            $csv .= sprintf("%s,%s,%s,%s\n", $r->number, '"'.str_replace('"','""',$r->title).'"', $r->status, $r->created_at);
        }
        return ['content' => $csv, 'filename' => 'reports_'.date('Ymd_His').'.csv', 'content_type' => 'text/csv'];
    }
}
