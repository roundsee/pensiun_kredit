<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_MARKETING = 'marketing';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_SUPPORT_BISNIS = 'support_bisnis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            // Keep behavior to ensure a provided `role` slug creates/sets `role_id`,
            // but avoid writing back to the `role` column from `role_id` to prevent
            // unexpected DB updates. The `role` column should be considered legacy
            // and can be cleared with the sync command when ready.
            if (filled($user->role)) {
                $roleId = DB::table('roles')->where('slug', $user->role)->value('id');

                if (! $roleId) {
                    $roleId = DB::table('roles')->insertGetId([
                        'name' => str($user->role)->replace('_', ' ')->title()->toString(),
                        'slug' => $user->role,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $user->role_id = $roleId;
            }
        });
    }

    public function roleRelation()
    {
        return $this->belongsTo('App\\Models\\Role', 'role_id');
    }

    public function roleSlug(): string
    {
        // Prefer the related `Role` slug when available; fall back to the legacy
        // `role` column only if the relation is not loaded or empty.
        return (string) ($this->roleRelation?->slug ?? $this->role ?? self::ROLE_MARKETING);
    }

    public function hasRole(string $role): bool
    {
        return $this->roleSlug() === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->roleSlug(), $roles, true);
    }

    public function canEditKbPricing(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_MARKETING,
            self::ROLE_SUPERVISOR,
            self::ROLE_SUPPORT_BISNIS,
        ]);
    }
}
