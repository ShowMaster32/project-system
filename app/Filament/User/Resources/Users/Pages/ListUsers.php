<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New User')
                ->visible(fn () => session('current_user_role') === 'admin' || auth()->user()?->isGlobalAdmin()),
        ];
    }

    public function getSubheading(): string | null
    {
        return 'Showing All Users';
    }
}
