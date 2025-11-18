<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Se è nella route di selezione progetto, lascia passare
        if ($request->routeIs('projects.selection')) {
            return $next($request);
        }

        // Se non ha un progetto selezionato, redirect a selezione
        if (!session()->has('current_project_id')) {
            return redirect()->route('projects.selection');
        }

        // Verifica che abbia accesso al progetto corrente
        $hasAccess = $user->projects()
            ->where('project_id', session('current_project_id'))
            ->exists();

        if (!$hasAccess) {
            session()->forget('current_project_id');
            return redirect()->route('projects.selection')
                ->with('error', 'Non hai accesso a questo progetto');
        }

        return $next($request);
    }
}