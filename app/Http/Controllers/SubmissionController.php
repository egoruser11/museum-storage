<?php

namespace App\Http\Controllers;

use App\Models\ArtifactSubmission;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(): View
    {
        return view('submissions.index', [
            'categories' => Category::query()->orderBy('name')->get(),
            'submissions' => Auth::user()
                ->submissions()
                ->with(['category', 'artifact'])
                ->latest()
                ->get(),
            'actions' => ArtifactSubmission::ACTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:64'],
            'desired_action' => ['required', Rule::in(array_keys(ArtifactSubmission::ACTIONS))],
            'desired_price' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:desired_action,'.ArtifactSubmission::ACTION_SELL,
                'prohibited_if:desired_action,'.ArtifactSubmission::ACTION_DONATE,
            ],
            'description' => ['required', 'string', 'max:5000'],
            'provenance' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($data['desired_action'] === ArtifactSubmission::ACTION_DONATE) {
            $data['desired_price'] = null;
        }

        Auth::user()->submissions()->create($data + [
            'status' => ArtifactSubmission::STATUS_NEW,
        ]);

        return redirect()
            ->route('submissions.index')
            ->with('success', 'Заявка на передачу предмета отправлена куратору.');
    }
}
