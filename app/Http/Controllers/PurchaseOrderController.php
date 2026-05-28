<?php

namespace App\Http\Controllers;

use App\Models\Artifact;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', [
            'orders' => Auth::user()
                ->purchaseOrders()
                ->with(['artifact.category'])
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request, Artifact $artifact): RedirectResponse
    {
        if (! $artifact->isAvailableForPurchase()) {
            throw ValidationException::withMessages([
                'artifact' => 'Этот предмет сейчас недоступен для выкупа.',
            ]);
        }

        if (Auth::user()->purchaseOrders()->where('artifact_id', $artifact->id)->exists()) {
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

        Auth::user()->purchaseOrders()->create($data + [
            'artifact_id' => $artifact->id,
            'offered_price' => $artifact->sale_price,
            'status' => PurchaseOrder::STATUS_NEW,
        ]);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Заявка на выкуп отправлена администратору.');
    }
}
