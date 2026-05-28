<?php

namespace App\Http\Controllers;

use App\Models\Artifact;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $artifacts = Artifact::query()
            ->with('category')
            ->whereIn('status', [
                Artifact::STATUS_IN_STORAGE,
                Artifact::STATUS_ON_SALE,
                Artifact::STATUS_RESTORATION,
            ])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('inventory_number', 'like', "%{$search}%")
                        ->orWhere('period', 'like', "%{$search}%")
                        ->orWhere('material', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('only_sale'), fn ($query) => $query->where('status', Artifact::STATUS_ON_SALE))
            ->orderByRaw("case when status = 'on_sale' then 0 else 1 end")
            ->orderBy('title')
            ->get();

        return view('catalog.index', [
            'artifacts' => $artifacts,
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category_id', 'only_sale']),
        ]);
    }

    public function show(Artifact $artifact): View
    {
        abort_if($artifact->status === Artifact::STATUS_SOLD, 404);

        return view('catalog.show', [
            'artifact' => $artifact->load('category'),
        ]);
    }
}
