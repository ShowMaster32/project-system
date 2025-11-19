<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::deleting(function (self $user) {
            try {
                \DB::table('archived_users')->insert([
                    'original_user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => (bool) $user->is_active,
                    'deleted_by' => auth()->id(),
                    'deleted_at' => now(),
                    'data_json' => json_encode([
                        'attributes' => $user->getAttributes(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Fail-safe: do not block deletion if archive fails
            }
        });
    }

    public function isGlobalAdmin(): bool
    {
        $emails = config('pmt.global_admin_emails', []);
        return in_array($this->email, $emails, true);
    }

    // CLassi aggiunte
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withPivot('role', 'is_active')
                    ->withTimestamps();
    }

    public function lastProject()
    {
        return $this->belongsTo(Project::class, 'last_project_id');
    }

    // Helper per ottenere ruolo in un progetto
    public function getRoleInProject($projectId)
    {
        return $this->projects()
                    ->where('projects.id', $projectId)
                    ->first()
                    ?->pivot
                    ?->role;
    }
}
