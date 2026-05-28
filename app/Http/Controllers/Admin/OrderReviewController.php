<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderReviewController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.orders.index', [
            'orders' => PurchaseOrder::query()
                ->with(['user', 'artifact.category'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->latest()
                ->get(),
            'statuses' => PurchaseOrder::STATUSES,
            'filters' => $request->only(['status']),
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(PurchaseOrder::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($purchaseOrder->user_id === $request->user()->id && in_array($data['status'], [
            PurchaseOrder::STATUS_APPROVED,
            PurchaseOrder::STATUS_PAID,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'Администратор не может подтвердить собственную заявку на выкуп. Апрув должен выполнить другой администратор.',
            ]);
        }

        DB::transaction(function () use ($purchaseOrder, $data): void {
            $purchaseOrder->update([
                'status' => $data['status'],
                'admin_note' => $data['admin_note'] ?? null,
                'processed_at' => now(),
            ]);

            if ($data['status'] === PurchaseOrder::STATUS_PAID) {
                $purchaseOrder->artifact()->update([
                    'status' => Artifact::STATUS_SOLD,
                    'sale_price' => $purchaseOrder->offered_price,
                ]);
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Статус заявки на выкуп обновлен.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Заявка на выкуп удалена из базы.');
    }
}
