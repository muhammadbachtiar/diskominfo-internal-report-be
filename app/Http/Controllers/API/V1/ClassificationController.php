<?php

namespace App\Http\Controllers\API\V1;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Letter\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class ClassificationController extends BaseController
{
    public function index()
    {
        try {
            CheckRolesAction::resolve()->execute('view-classification');
            
            $classifications = Classification::all();
            return $this->resolveForSuccessResponseWith('Classifications', $classifications);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('add-classification');
            
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:classifications,name',
                'description' => 'nullable|string',
            ]);

            $classification = Classification::create($data);

            return $this->resolveForSuccessResponseWith('Classification created', $classification, HttpStatus::Created);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            CheckRolesAction::resolve()->execute('view-classification');
            
            $classification = Classification::findOrFail($id);
            return $this->resolveForSuccessResponseWith('Classification', $classification);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            CheckRolesAction::resolve()->execute('edit-classification');
            
            $classification = Classification::findOrFail($id);
            
            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:classifications,name,' . $id,
                'description' => 'nullable|string',
            ]);

            $classification->update($data);

            return $this->resolveForSuccessResponseWith('Classification updated', $classification);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            CheckRolesAction::resolve()->execute('delete-classification');
            
            $classification = Classification::findOrFail($id);
            $classification->delete();
            
            return $this->resolveForSuccessResponseWith('Classification deleted', null);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
