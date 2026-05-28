<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return $this->view();
    }

    public function store(Request $request): RedirectResponse
    {
        Category::query()->create($this->validated($request));

        return redirect()->route('admin.categories.index')->with('success', 'Категория добавлена.');
    }

    public function edit(Category $category): View
    {
        return $this->view($category);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'Категория обновлена.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->artifacts()->exists() || $category->submissions()->exists()) {
            return back()->withErrors(['category' => 'Нельзя удалить категорию, связанную с экспонатами или заявками.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Категория удалена.');
    }

    private function view(?Category $editingCategory = null): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->withCount(['artifacts', 'submissions'])
                ->orderBy('name')
                ->get(),
            'editingCategory' => $editingCategory,
        ]);
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.($category?->id ?? 'NULL')],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
