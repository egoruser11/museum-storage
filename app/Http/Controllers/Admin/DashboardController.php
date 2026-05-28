<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'categoriesCount' => Category::query()->count(),
            'artifactsCount' => Artifact::query()->count(),
            'saleCount' => Artifact::query()->where('status', Artifact::STATUS_ON_SALE)->count(),
            'newSubmissionsCount' => ArtifactSubmission::query()->where('status', ArtifactSubmission::STATUS_NEW)->count(),
            'newOrdersCount' => PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_NEW)->count(),
            'usersCount' => User::query()->where('role', User::ROLE_USER)->count(),
            'blockedUsersCount' => User::query()->whereNotNull('blocked_at')->count(),
            'latestSubmissions' => ArtifactSubmission::query()->with(['user', 'category'])->latest()->limit(5)->get(),
            'latestOrders' => PurchaseOrder::query()->with(['user', 'artifact'])->latest()->limit(5)->get(),
        ]);
    }
}
