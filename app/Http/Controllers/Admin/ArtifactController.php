<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtifactController extends Controller
{
    public function index(Request $request): View
    {
        return $this->view($request);
    }

    public function store(Request $request): RedirectResponse
    {
        Artifact::query()->create($this->validated($request) + [
            'inventory_number' => Artifact::nextInventoryNumber(),
        ]);

        return redirect()->route('admin.artifacts.index')->with('success', 'Экспонат добавлен в фонд.');
    }

    public function edit(Request $request, Artifact $artifact): View
    {
        return $this->view($request, $artifact);
    }

    public function update(Request $request, Artifact $artifact): RedirectResponse
    {
        $artifact->update($this->validated($request));

        return redirect()->route('admin.artifacts.index')->with('success', 'Карточка экспоната обновлена.');
    }

    public function destroy(Artifact $artifact): RedirectResponse
    {
        $artifact->delete();

        return redirect()->route('admin.artifacts.index')->with('success', 'Экспонат удален.');
    }

    private function view(Request $request, ?Artifact $editingArtifact = null): View
    {
        $artifacts = Artifact::query()
            ->with(['category', 'owner'])
            ->withCount('purchaseOrders')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('inventory_number', 'like', "%{$search}%")
                        ->orWhere('period', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->orderBy('title')
            ->get();

        return view('admin.artifacts.index', [
            'artifacts' => $artifacts,
            'categories' => Category::query()->orderBy('name')->get(),
            'editingArtifact' => $editingArtifact,
            'filters' => $request->only(['search', 'category_id', 'status']),
            'statuses' => Artifact::STATUSES,
            'acquisitionTypes' => Artifact::ACQUISITION_TYPES,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'period' => ['nullable', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:255'],
            'condition_state' => ['required', 'string', 'max:255'],
            'acquisition_type' => ['required', Rule::in(array_keys(Artifact::ACQUISITION_TYPES))],
            'status' => ['required', Rule::in(array_keys(Artifact::STATUSES))],
            'appraised_value' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
