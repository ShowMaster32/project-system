<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\Widget;

class ProjectInfoWidget extends Widget
{
    protected string $view = 'user.widgets.project-info-widget';

    protected int | string | array $columnSpan = 'full';

    public function getProjectInfo(): array
    {
        return [
            'code' => session('current_project_code', 'N/A'),
            'name' => session('current_project_name', 'Nessun progetto'),
            'role' => ucfirst(session('current_user_role', 'user')),
        ];
    }
}
