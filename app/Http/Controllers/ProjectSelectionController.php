<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectSelectionController extends Controller
{
    /**
     * Mostra pagina di selezione progetto
     */
    public function select()
    {
        $user = auth()->user();
        
        // Progetti accessibili dall'utente
        $projects = $user->projects()
            ->where('project_user.is_active', true)
            ->where('projects.is_active', true)
            ->withPivot('role')
            ->get();

        // Se ha un solo progetto, entra direttamente
        if ($projects->count() === 1) {
            return $this->enter($projects->first()->id);
        }

        // Se ha un last_project_id valido, evidenzialo
        $lastProjectId = $user->last_project_id;

        return view('projects.select', compact('projects', 'lastProjectId'));
    }

    /**
     * Entra in un progetto specifico
     */
    public function enter(Request $request, $projectId = null)
    {
        // Se chiamato da select() direttamente, $projectId è già passato
        // Altrimenti viene dalla route con Request
        if ($projectId === null) {
            $projectId = $request->route('project');
        }

        $user = auth()->user();

        // Verifica che l'utente abbia accesso
        $project = $user->projects()
            ->where('projects.id', $projectId)
            ->where('project_user.is_active', true)
            ->where('projects.is_active', true)
            ->withPivot('role')
            ->first();

        if (!$project) {
            return redirect()->route('projects.select')
                ->with('error', 'Non hai accesso a questo progetto');
        }

        // Imposta progetto corrente in sessione
        session([
            'current_project_id' => $project->id,
            'current_project_code' => $project->code,
            'current_project_name' => $project->name,
            'current_user_role' => $project->pivot->role,
        ]);

        // Aggiorna last_project_id dell'utente
        $user->update(['last_project_id' => $project->id]);

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', "Benvenuto in {$project->name}");
    }

    /**
     * Switch a un altro progetto
     */
    public function switch(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        return $this->enter($request, $request->project_id);
    }
}