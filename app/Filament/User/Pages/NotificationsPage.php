<?php

namespace App\Filament\User\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsPage extends Page
{
    protected string $view = 'filament.user.pages.notifications';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Notifiche';
    protected static ?string $slug            = 'notifiche';
    protected static ?int    $navigationSort  = 9;

    public int $unreadCount = 0;

    public static function getNavigationBadge(): ?string
    {
        $count = auth()->user()?->unreadNotifications()->count() ?? 0;
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public function mount(): void
    {
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
    }

    public function getNotifications()
    {
        return auth()->user()
            ->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(function (DatabaseNotification $notification) {
                $data = $notification->data;
                return [
                    'id'       => $notification->id,
                    'read'     => ! is_null($notification->read_at),
                    'time'     => $notification->created_at->diffForHumans(),
                    'created'  => $notification->created_at->format('d/m/Y H:i'),
                    'title'    => $data['title']  ?? 'Notifica',
                    'body'     => $data['body']   ?? '',
                    'icon'     => $data['icon']   ?? 'heroicon-o-bell',
                    'color'    => $data['color']  ?? 'gray',
                    'url'      => $data['url']    ?? null,
                    'type'     => $data['type']   ?? 'generic',
                ];
            });
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->unreadCount = 0;
        $this->dispatch('notifications-read');
    }

    public function markRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        $this->unreadCount = auth()->user()->unreadNotifications()->count();
    }

    public function deleteNotification(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->delete();
    }

    public function deleteAll(): void
    {
        auth()->user()->notifications()->delete();
        $this->unreadCount = 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('Segna tutte come lette')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('markAllRead')
                ->visible(fn () => auth()->user()->unreadNotifications()->count() > 0),

            Action::make('delete_all')
                ->label('Elimina tutte')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Eliminare tutte le notifiche?')
                ->modalDescription('Questa azione non è reversibile.')
                ->action('deleteAll'),
        ];
    }
}
