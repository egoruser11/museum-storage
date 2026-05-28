<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubmissionReviewController extends Controller
{
    public function index(Request $request): View
    {
        $submissions = ArtifactSubmission::query()
            ->with(['user', 'category', 'artifact', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('action'), fn ($query) => $query->where('desired_action', $request->query('action')))
            ->latest()
            ->get();

        return view('admin.submissions.index', [
            'submissions' => $submissions,
            'categories' => Category::query()->orderBy('name')->get(),
            'statuses' => ArtifactSubmission::STATUSES,
            'actions' => ArtifactSubmission::ACTIONS,
            'filters' => $request->only(['status', 'action']),
        ]);
    }

    public function update(Request $request, ArtifactSubmission $artifactSubmission): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', Rule::in(array_keys(ArtifactSubmission::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($data['status'] === ArtifactSubmission::STATUS_ACCEPTED && ! ($data['category_id'] ?? $artifactSubmission->category_id)) {
            throw ValidationException::withMessages([
                'status' => 'Перед принятием заявки нужно выбрать категорию предмета.',
            ]);
        }

        if ($data['status'] === ArtifactSubmission::STATUS_ACCEPTED && $artifactSubmission->user_id === Auth::id()) {
            throw ValidationException::withMessages([
                'status' => 'Администратор не может принять собственную заявку. Подтвердить ее должен другой администратор.',
            ]);
        }

        DB::transaction(function () use ($artifactSubmission, $data): void {
            $artifactSubmission->fill([
                'status' => $data['status'],
                'category_id' => ($data['category_id'] ?? null) ?: $artifactSubmission->category_id,
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            if ($data['status'] === ArtifactSubmission::STATUS_ACCEPTED && ! $artifactSubmission->artifact_id) {
                $artifact = Artifact::query()->create([
                    'category_id' => $artifactSubmission->category_id,
                    'owner_id' => $artifactSubmission->user_id,
                    'title' => $artifactSubmission->title,
                    'inventory_number' => Artifact::nextInventoryNumber(),
                    'period' => 'Уточняется',
                    'material' => 'Уточняется',
                    'condition_state' => 'Требует фондовой экспертизы',
                    'acquisition_type' => $artifactSubmission->desired_action === ArtifactSubmission::ACTION_SELL ? 'purchase' : 'donation',
                    'status' => Artifact::STATUS_IN_STORAGE,
                    'appraised_value' => $artifactSubmission->desired_price ?? 0,
                    'description' => $artifactSubmission->description."\n\nПроисхождение: ".($artifactSubmission->provenance ?: 'не указано'),
                ]);

                $artifactSubmission->artifact_id = $artifact->id;
            }

            $artifactSubmission->save();
        });

        return redirect()->route('admin.submissions.index')->with('success', 'Статус заявки обновлен.');
    }

    public function destroy(ArtifactSubmission $artifactSubmission): RedirectResponse
    {
        $artifactSubmission->delete();

        return redirect()->route('admin.submissions.index')->with('success', 'Заявка на передачу удалена из базы.');
    }
}
