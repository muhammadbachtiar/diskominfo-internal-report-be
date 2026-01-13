<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Letter\Models\Classification;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    public function index()
    {
        CheckRolesAction::resolve()->execute('view-classification');
        
        $classifications = Classification::all();
        return response()->json($classifications);
    }

    public function store(Request $request)
    {
        CheckRolesAction::resolve()->execute('add-classification');
        
        $request->validate([
            'name' => 'required|string|max:255|unique:classifications,name',
            'description' => 'nullable|string',
        ]);

        $classification = Classification::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($classification, 201);
    }

    public function show($id)
    {
        CheckRolesAction::resolve()->execute('view-classification');
        
        $classification = Classification::findOrFail($id);
        return response()->json($classification);
    }

    public function update(Request $request, $id)
    {
        CheckRolesAction::resolve()->execute('edit-classification');
        
        $classification = Classification::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:classifications,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $classification->update($request->only(['name', 'description']));

        return response()->json($classification);
    }

    public function destroy($id)
    {
        CheckRolesAction::resolve()->execute('delete-classification');
        
        $classification = Classification::findOrFail($id);
        $classification->delete();
        
        return response()->json(['message' => 'Classification deleted']);
    }
}
