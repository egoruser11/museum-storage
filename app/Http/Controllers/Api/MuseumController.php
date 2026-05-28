<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MuseumController extends Controller
{
    public function artifacts(Request $request): JsonResponse
    {
        $artifacts = Artifact::query()
            ->with('category')
            ->whereIn('status', [
                Artifact::STATUS_IN_STORAGE,
                Artifact::STATUS_ON_SALE,
                Artifact::STATUS_RESTORATION,
            ])
            ->when($request->boolean('only_sale'), fn ($query) => $query->where('status', Artifact::STATUS_ON_SALE))
            ->orderBy('title')
            ->get();

        return response()->json(['data' => $artifacts]);
    }

    public function submissions(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()
                ->submissions()
                ->with(['category', 'artifact'])
                ->latest()
                ->get(),
        ]);
    }

    public function storeSubmission(Request $request): JsonResponse
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

        $submission = $request->user()->submissions()->create($data + [
            'status' => ArtifactSubmission::STATUS_NEW,
        ]);

        return response()->json(['data' => $submission], 201);
    }

    public function orders(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()
                ->purchaseOrders()
                ->with(['artifact.category'])
                ->latest()
                ->get(),
        ]);
    }

    public function storeOrder(Request $request, Artifact $artifact): JsonResponse
    {
        if (! $artifact->isAvailableForPurchase()) {
            throw ValidationException::withMessages([
                'artifact' => 'Этот предмет сейчас недоступен для выкупа.',
            ]);
        }

        if ($request->user()->purchaseOrders()->where('artifact_id', $artifact->id)->exists()) {
            throw ValidationException::withMessages([
                'artifact' => 'Вы уже отправили заявку на выкуп этого предмета.',
            ]);
        }

        $data = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = $request->user()->purchaseOrders()->create($data + [
            'artifact_id' => $artifact->id,
            'offered_price' => $artifact->sale_price,
            'status' => PurchaseOrder::STATUS_NEW,
        ]);

        return response()->json(['data' => $order], 201);
    }
}
