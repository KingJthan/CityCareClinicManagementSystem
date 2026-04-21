<?php

namespace App\Http\Controllers;

use App\Models\DrugCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DrugCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = DrugCategory::withCount('drugs')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pharmacy.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pharmacy.categories.form', ['category' => new DrugCategory()]);
    }

    public function store(Request $request)
    {
        DrugCategory::create($this->validated($request));

        return redirect()->route('drug-categories.index')->with('success', 'Drug category created.');
    }

    public function edit(DrugCategory $drugCategory)
    {
        return view('pharmacy.categories.form', ['category' => $drugCategory]);
    }

    public function update(Request $request, DrugCategory $drugCategory)
    {
        $drugCategory->update($this->validated($request, $drugCategory));

        return redirect()->route('drug-categories.index')->with('success', 'Drug category updated.');
    }

    public function destroy(DrugCategory $drugCategory)
    {
        $drugCategory->delete();

        return redirect()->route('drug-categories.index')->with('success', 'Drug category archived.');
    }

    private function validated(Request $request, ?DrugCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('drug_categories', 'name')->ignore($category)],
            'code' => ['required', 'string', 'max:30', Rule::unique('drug_categories', 'code')->ignore($category)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['code'] = strtoupper($data['code']);

        return $data;
    }
}
