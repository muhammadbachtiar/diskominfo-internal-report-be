<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Domain\Letter\Actions\AnalyzeLetterAction;
use Domain\Letter\Actions\CreateLetterAction;
use Infra\Letter\Models\Letter;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LetterController extends Controller
{
    public function analyze(Request $request, AnalyzeLetterAction $action)
    {
        CheckRolesAction::resolve()->execute('analyze-letter');
        
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', 
        ]);

        try {
            $result = $action->execute($request->file('file'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Analysis failed: ' . $e->getMessage()], 500);
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
        $request->validate([
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

        $data = $request->all();
        $data['type'] = $type;
        $data['created_by'] = auth()->id() ?? 1; // Fallback for dev if no auth

        DB::beginTransaction();
        try {
            $letter = $action->execute($data, $request->file('file'));
            DB::commit();
            return response()->json($letter, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create letter: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        CheckRolesAction::resolve()->execute('view-letter');
        
        $query = Letter::with(['classification', 'unit', 'creator']);

        if ($request->has('unit_id')) {
            $query->byUnit($request->unit_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
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

        return response()->json($query->paginate(15));
    }

    public function show($id)
    {
        CheckRolesAction::resolve()->execute('view-letter');
        
        $letter = Letter::with(['classification', 'unit', 'creator'])->findOrFail($id);
        return response()->json($letter);
    }
    
    public function update(Request $request, $id)
    {
        CheckRolesAction::resolve()->execute('edit-letter');
        
        $letter = Letter::findOrFail($id);
        
        $request->validate([
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

        $letter->update($request->all());
        
        return response()->json($letter);
    }

    public function destroy($id)
    {
        CheckRolesAction::resolve()->execute('delete-letter');
        
        $letter = Letter::findOrFail($id);
        $letter->delete();
        return response()->json(['message' => 'Letter deleted']);
    }
}
