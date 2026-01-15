<?php

namespace App\Http\Controllers\API\V1;

use Domain\Letter\Actions\AnalyzeLetterAction;
use Domain\Letter\Actions\CreateLetterAction;
use Infra\Letter\Models\Letter;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class LetterController extends BaseController
{
    public function analyze(Request $request, AnalyzeLetterAction $action)
    {
        try {
            CheckRolesAction::resolve()->execute('analyze-letter');
            
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'type' => 'required|string|in:incoming,outgoing', // incoming = surat masuk, outgoing = surat keluar
            ]);

            $result = $action->execute(
                $request->file('file'),
                $request->input('type')
            );
            
            return $this->resolveForSuccessResponseWith('Letter analysis completed', $result);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function storeIncoming(Request $request, CreateLetterAction $action)
    {
        CheckRolesAction::resolve()->execute('add-incoming-letter');

        return $this->store($request, $action, 'incoming');
    }

    public function storeOutgoing(Request $request, CreateLetterAction $action)
    {
        CheckRolesAction::resolve()->execute('add-outgoing-letter');

        return $this->store($request, $action, 'outgoing');
    }

    private function store(Request $request, CreateLetterAction $action, string $type)
    {
        try {
            $data = $request->validate([
                'letter_number' => 'required|string',
                'sender_receiver' => 'required|string',
                'date_of_letter' => 'required|date',
                'year' => 'required|integer',
                'subject' => 'required|string',
                'classification_id' => 'required|exists:classifications,id',
                'unit_id' => 'nullable|exists:units,id',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480', // 20MB
                'metadata_ai' => 'nullable|array',
            ]);

            $data['type'] = $type;
            $data['created_by'] = auth()->id() ?? 1; // Fallback for dev if no auth

            DB::beginTransaction();
            $letter = $action->execute($data, $request->file('file'));
            DB::commit();
            
            return $this->resolveForSuccessResponseWith('Letter created', $letter, HttpStatus::Created);
        } catch (ValidationException $th) {
            DB::rollBack();
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('view-letter');
            
            $query = Letter::with(['classification', 'unit', 'creator']);

            if ($request->has('unit_id')) {
                $query->byUnit($request->unit_id);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('classification_id')) {
                $query->where('classification_id', $request->classification_id);
            }

            if ($request->has('from') && $request->has('to')) {
                $query->whereBetween('date_of_letter', [$request->from, $request->to]);
            } elseif ($request->has('from')) {
                $query->where('date_of_letter', '>=', $request->from);
            } elseif ($request->has('to')) {
                $query->where('date_of_letter', '<=', $request->to);
            }

            if ($request->has('year')) {
                $query->where('year', $request->year);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhere('letter_number', 'like', "%{$search}%")
                      ->orWhere('sender_receiver', 'like', "%{$search}%");
                });
            }

            $paginated = $query->paginate(15);
            return $this->resolveForSuccessResponseWithPage('Letters', $paginated);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            CheckRolesAction::resolve()->execute('view-letter');
            
            $letter = Letter::with(['classification', 'unit', 'creator'])->findOrFail($id);
            return $this->resolveForSuccessResponseWith('Letter', $letter);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            CheckRolesAction::resolve()->execute('edit-letter');
            
            $letter = Letter::findOrFail($id);
            
            $data = $request->validate([
                'letter_number' => 'string',
                'sender_receiver' => 'string',
                'date_of_letter' => 'date',
                'year' => 'integer',
                'subject' => 'string',
                'classification_id' => 'exists:classifications,id',
                'unit_id' => 'nullable|exists:units,id',
                'description' => 'nullable|string',
                'metadata_ai' => 'nullable|array',
            ]);

            $letter->update($data);
            
            return $this->resolveForSuccessResponseWith('Letter updated', $letter);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            CheckRolesAction::resolve()->execute('delete-letter');
            
            $letter = Letter::findOrFail($id);
            $letter->delete();
            
            return $this->resolveForSuccessResponseWith('Letter deleted', null);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
