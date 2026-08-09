<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoordinatorType;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CoordinatorTypeController extends Controller
{
    public function index()
    {
        $types = CoordinatorType::with('creator')
            ->withCount('users')
            ->latest()
            ->get();

        return view('admin.coordinator-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:coordinator_types,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        CoordinatorType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.coordinator-types.index')->with('success', 'Coordinator Type created successfully.');
    }

    public function update(Request $request, CoordinatorType $coordinatorType)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('coordinator_types')->ignore($coordinatorType->id)],
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $coordinatorType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.coordinator-types.index')->with('success', 'Coordinator Type updated successfully.');
    }

    public function toggleStatus(CoordinatorType $coordinatorType)
    {
        $newStatus = ($coordinatorType->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $coordinatorType->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'Coordinator Type activated.' : 'Coordinator Type deactivated.';
        return redirect()->route('admin.coordinator-types.index')->with('success', $msg);
    }

    public function destroy(CoordinatorType $coordinatorType)
    {
        if ($coordinatorType->users()->count() > 0) {
            return redirect()->route('admin.coordinator-types.index')
                ->with('error', 'Cannot delete Coordinator Type that is assigned to existing coordinator accounts.');
        }

        $coordinatorType->delete();

        return redirect()->route('admin.coordinator-types.index')->with('success', 'Coordinator Type deleted successfully.');
    }
}
