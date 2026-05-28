<?php

namespace App\Http\Controllers;

use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'categoriesCount' => Category::query()->count(),
            'artifactsCount' => Artifact::query()->count(),
            'availableCount' => Artifact::query()->where('status', Artifact::STATUS_ON_SALE)->count(),
            'submissionsCount' => ArtifactSubmission::query()->count(),
        ]);
    }
}
