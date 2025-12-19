<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\CreateDraftReportAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreateReportController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = $req->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'category' => 'nullable|string',
                'location' => 'nullable|string',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric',
                'event_at' => 'nullable|date',
                'unit_id' => 'nullable|uuid',
                'category_id' => 'nullable|uuid',
            ]);
            $report = CreateDraftReportAction::resolve()->execute($data);
            return $this->resolveForSuccessResponseWith('Draft created', $report, HttpStatus::Created);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (BadRequestException $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), status: HttpStatus::BadRequest);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
