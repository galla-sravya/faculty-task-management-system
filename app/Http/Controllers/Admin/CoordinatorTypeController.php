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
        $types = CoordinatorType::with('creator')->latest()->get();
        return view('admin.coordinator-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.coordinator-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:coordinator_types,name',
            'description' => 'nullable|string',
        ]);

        CoordinatorType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.coordinator-types.index')->with('success', 'Coordinator Type created successfully.');
    }

    public function edit(CoordinatorType $coordinatorType)
    {
        return view('admin.coordinator-types.edit', compact('coordinatorType'));
    }

    public function update(Request $request, CoordinatorType $coordinatorType)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('coordinator_types')->ignore($coordinatorType->id)],
            'description' => 'nullable|string',
        ]);

        $coordinatorType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.coordinator-types.index')->with('success', 'Coordinator Type updated successfully.');
    }

    public function destroy(CoordinatorType $coordinatorType)
    {
        $coordinatorType->delete();
        return redirect()->route('admin.coordinator-types.index')->with('success', 'Coordinator Type deleted successfully.');
    }
}
