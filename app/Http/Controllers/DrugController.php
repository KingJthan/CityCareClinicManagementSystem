<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DrugController extends Controller
{
    public function index(Request $request)
    {
        $drugs = Drug::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('strength', 'like', "%{$search}%");
                });
            })
            ->when($request->drug_category_id, fn ($query, $categoryId) => $query->where('drug_category_id', $categoryId))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pharmacy.drugs.index', [
            'drugs' => $drugs,
            'categories' => DrugCategory::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('pharmacy.drugs.form', $this->formData(new Drug(['status' => 'active'])));
    }

    public function store(Request $request)
    {
        Drug::create($this->validated($request));

        return redirect()->to(workspace_route('drugs.index'))->with('success', 'Drug added to pharmacy inventory.');
    }

    public function edit(Drug $drug)
    {
        return view('pharmacy.drugs.form', $this->formData($drug));
    }

    public function update(Request $request, Drug $drug)
    {
        $drug->update($this->validated($request, $drug));

        return redirect()->to(workspace_route('drugs.index'))->with('success', 'Drug updated.');
    }

    public function destroy(Drug $drug)
    {
        $drug->delete();

        return redirect()->to(workspace_route('drugs.index'))->with('success', 'Drug archived.');
    }

    private function formData(Drug $drug): array
    {
        return [
            'drug' => $drug,
            'categories' => DrugCategory::where('status', 'active')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Drug $drug = null): array
    {
        return $request->validate([
            'drug_category_id' => ['required', 'exists:drug_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('drugs', 'name')
                    ->where('strength', $request->input('strength'))
                    ->where('dosage_form', $request->input('dosage_form'))
                    ->ignore($drug),
            ],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'strength' => ['required', 'string', 'max:80'],
            'dosage_form' => ['required', 'string', 'max:80'],
            'unit' => ['required', 'string', 'max:40'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
