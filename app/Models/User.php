<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'notification_preferences',
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
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Cascade soft delete to merchant
            if ($user->isMerchant() && $user->merchant) {
                $user->merchant->delete();
            }
        });

        static::restoring(function (User $user) {
            // Restore merchant when user is restored
            if ($user->isMerchant() && $user->merchant()->withTrashed()->exists()) {
                $user->merchant()->withTrashed()->restore();
            }
        });
    }

    /**
     * Get the merchant associated with the user.
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, 'user_id');
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the user's refresh tokens.
     */
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    /**
     * Create a new refresh token.
     */
    public function createRefreshToken(): string
    {
        $token = hash('sha256', bin2hex(random_bytes(64)));

        $this->refreshTokens()->create([
            'token' => $token,
            'hashed_token' => hash('sha256', $token),
            'expires_at' => now()->addDays(30),
        ]);

        return $token;
    }

    /**
     * Validate a refresh token.
     */
    public function validateRefreshToken(string $token): ?RefreshToken
    {
        $hashedToken = hash('sha256', $token);

        return $this->refreshTokens()
            ->where('hashed_token', $hashedToken)
            ->where('revoked_at', null)
            ->where(function ($query) {
                $query->where('expires_at', null)
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Revoke all refresh tokens.
     */
    public function revokeAllRefreshTokens(): void
    {
        $this->refreshTokens()->update(['revoked_at' => now()]);
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is Merchant.
     */
    public function isMerchant(): bool
    {
        return $this->role === 'merchant';
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayName(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'merchant' => 'Merchant',
            default => 'Unknown',
        };
    }

    /**
     * Prevent Super Admin from being deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->isSuperAdmin();
    }

    /**
     * Prevent Super Admin role from being changed.
     */
    public function canChangeRole(): bool
    {
        return !$this->isSuperAdmin();
    }

    /**
     * Check if user is active (not soft deleted).
     */
    public function isActive(): bool
    {
        return is_null($this->deleted_at);
    }

    /**
     * Get user's last login time.
     */
    public function getLastLoginAttribute()
    {
        return $this->activityLogs()
            ->where('action', ActivityLog::ACTION_LOGIN)
            ->latest()
            ->value('created_at');
    }
}

