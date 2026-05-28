<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\Category;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $categoryRows = Category::query()
            ->withCount(['artifacts', 'submissions'])
            ->orderBy('name')
            ->get();

        $submissionRows = ArtifactSubmission::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $orderRows = PurchaseOrder::query()
            ->select('status', DB::raw('count(*) as total'), DB::raw('coalesce(sum(offered_price), 0) as amount'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        return view('admin.reports.index', [
            'categoryRows' => $categoryRows,
            'submissionRows' => $submissionRows,
            'orderRows' => $orderRows,
            'artifactStatuses' => Artifact::STATUSES,
            'submissionStatuses' => ArtifactSubmission::STATUSES,
            'orderStatuses' => PurchaseOrder::STATUSES,
            'revenue' => PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_PAID)->sum('offered_price'),
            'availableForSale' => Artifact::query()->where('status', Artifact::STATUS_ON_SALE)->count(),
        ]);
    }
}
