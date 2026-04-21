<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::query()
            ->withCount(['doctors', 'appointments'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.form', ['department' => new Department()]);
    }

    public function store(Request $request)
    {
        Department::create($this->validated($request));

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function show(Department $department)
    {
        $department->load(['doctors.user'])->loadCount('appointments');

        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('departments.form', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $department->update($this->validated($request, $department));

        return redirect()->route('departments.show', $department)->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department archived.');
    }

    private function validated(Request $request, ?Department $department = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department)],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department)],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['code'] = strtoupper($data['code']);

        return $data;
    }
}
