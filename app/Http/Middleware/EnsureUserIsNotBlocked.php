<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBlocked()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ваш аккаунт заблокирован. Подача заявок и выкуп недоступны.',
                ], 403);
            }

            return redirect()
                ->route('home')
                ->withErrors(['account' => 'Ваш аккаунт заблокирован. Подача заявок и выкуп недоступны.']);
        }

        return $next($request);
    }
}
