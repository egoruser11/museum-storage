<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->withCount(['submissions', 'purchaseOrders'])
                ->when($request->filled('search'), function ($query) use ($request): void {
                    $search = $request->string('search');
                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when($request->query('status') === 'blocked', fn ($query) => $query->whereNotNull('blocked_at'))
                ->when($request->query('status') === 'active', fn ($query) => $query->whereNull('blocked_at'))
                ->orderBy('name')
                ->get(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function block(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Администраторов блокировать нельзя.']);
        }

        $user->update(['blocked_at' => now()]);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь заблокирован.');
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update(['blocked_at' => null]);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь разблокирован.');
    }
}
