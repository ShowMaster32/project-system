<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class CheckProjectAdminRole
{
    /**
     * Verifica che l'utente abbia ruolo 'admin' nel progetto corrente
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Consenti le rotte di autenticazione di Filament (login, logout, password...) per evitare loop
        if ($request->routeIs('filament.*.auth.*')) {
            return $next($request);
        }

        if (!$user) {
            $loginUrl = Filament::getCurrentPanel()?->getLoginUrl();
            if (!$loginUrl) {
                $path = trim($request->path(), '/');
                $loginUrl = str_starts_with($path, 'admin') ? url('/admin/login') : url('/app/login');
            }
            return redirect()->to($loginUrl);
        }

        // Se non ha un progetto selezionato, redirect a selezione
        if (!session()->has('current_project_id')) {
            return redirect()->route('projects.select');
        }

        $projectId = session('current_project_id');
        $role = session('current_user_role');

        // Verifica che sia admin nel progetto corrente
        if ($role !== 'admin') {
            // Non è admin, redirect al panel utenti
            return redirect()->route('filament.user.pages.dashboard')
                ->with('error', 'Non hai i permessi per accedere all\'area amministrazione');
        }

        return $next($request);
    }
}
