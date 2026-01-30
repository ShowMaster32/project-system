<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'User Details: ' . $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        $role = session('current_user_role');
        $isGlobalAdmin = auth()->user()?->isGlobalAdmin();
        $canManage = ($role === 'admin') || $isGlobalAdmin;

        return [
            Actions\EditAction::make()
                ->visible($canManage),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $record = $this->record;
        $currentProjectId = session('current_project_id');
        $isGlobalAdmin = $record->isGlobalAdmin();

        return $schema
            ->schema([
                Section::make('User Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('User ID'),
                                    
                                TextEntry::make('name')
                                    ->label('Username')
                                    ->weight('bold'),
                                    
                                TextEntry::make('email')
                                    ->label('Email Address')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),
                                    
                                TextEntry::make('is_active')
                                    ->label('Account Status')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),
                                    
                                TextEntry::make('access_level')
                                    ->label('Access Level')
                                    ->badge()
                                    ->color(fn (): string => $isGlobalAdmin ? 'warning' : 'info')
                                    ->default(function () use ($isGlobalAdmin) {
                                        if ($isGlobalAdmin) {
                                            return 'Global Administrator';
                                        }
                                        $role = session('current_user_role', 'user');
                                        return ucfirst($role);
                                    }),
                                    
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d/m/Y H:i'),
                                    
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d/m/Y H:i')
                                    ->since(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Current Project Role')
                    ->schema([
                        TextEntry::make('current_role')
                            ->label('Role in Current Project')
                            ->badge()
                            ->color(fn (string $state): string => match($state) {
                                'admin' => 'danger',
                                'coordinator' => 'warning',
                                'wp_leader' => 'info',
                                'task_leader' => 'success',
                                default => 'gray',
                            })
                            ->default(function () use ($record, $currentProjectId) {
                                if (!$currentProjectId) {
                                    return 'No project selected';
                                }
                                
                                $role = $record->getRoleInProject($currentProjectId);
                                return $role ? ucfirst(str_replace('_', ' ', $role)) : 'No role assigned';
                            }),
                    ])
                    ->visible(fn () => $currentProjectId !== null)
                    ->collapsible(),

                Section::make('All Project Memberships')
                    ->schema([
                        TextEntry::make('projects_list')
                            ->label('Projects')
                            ->default(function () use ($record) {
                                $projects = $record->projects()
                                    ->select(['projects.id', 'projects.code', 'projects.name'])
                                    ->withPivot(['role', 'is_active'])
                                    ->orderBy('projects.name')
                                    ->get();

                                if ($projects->isEmpty()) {
                                    return 'No project memberships';
                                }

                                return $projects->map(function ($project) {
                                    $status = $project->pivot->is_active ? '✓ Active' : '✗ Inactive';
                                    $role = ucfirst(str_replace('_', ' ', $project->pivot->role));
                                    $code = $project->code ? "[{$project->code}] " : '';
                                    
                                    return "{$code}{$project->name} — Role: {$role} ({$status})";
                                })->join("\n");
                            })
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn (string $state): string => 
                                '<div class="whitespace-pre-line">' . e($state) . '</div>'
                            ),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}
