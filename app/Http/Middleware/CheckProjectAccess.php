<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class CheckProjectAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Allow Filament authentication routes to pass through without project checks
        // to avoid redirect loops (e.g., /app/login, /admin/login, password reset, etc.).
        if ($request->routeIs('filament.*.auth.*')) {
            return $next($request);
        }

        if (!$user) {
            // Panel-aware login URL resolution
            $loginUrl = Filament::getCurrentPanel()?->getLoginUrl();

            if (!$loginUrl) {
                // Fallback based on path prefix
                $path = trim($request->path(), '/');
                if (str_starts_with($path, 'admin')) {
                    $loginUrl = url('/admin/login');
                } elseif (str_starts_with($path, 'app')) {
                    $loginUrl = url('/app/login');
                } else {
                    // Default to user panel login
                    $loginUrl = url('/app/login');
                }
            }

            return redirect()->to($loginUrl);
        }

        // Se è nella route di selezione progetto, lascia passare
        if ($request->routeIs('projects.select') ||
            $request->routeIs('projects.enter') ||
            $request->routeIs('projects.switch')) {
            return $next($request);
        }

        // Se non ha un progetto selezionato, redirect a selezione
        if (!session()->has('current_project_id')) {
            return redirect()->route('projects.select');
        }

        // Verifica che abbia accesso al progetto corrente
        $hasAccess = $user->projects()
            ->where('project_id', session('current_project_id'))
            ->where('project_user.is_active', true)
            ->exists();

        if (!$hasAccess) {
            session()->forget([
                'current_project_id',
                'current_project_code',
                'current_project_name',
                'current_user_role',
            ]);

            return redirect()->route('projects.select')
                ->with('error', 'Non hai più accesso a questo progetto');
        }

        return $next($request);
    }
}
