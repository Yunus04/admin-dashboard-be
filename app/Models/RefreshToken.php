<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'hashed_token',
        'user_id',
        'expires_at',
        'revoked_at',
    ];

    protected $hidden = [
        'token',
        'hashed_token',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the refresh token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is valid (not expired and not revoked).
     */
    public function isValid(): bool
    {
        return !$this->revoked_at && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Revoke the token.
     */
    public function revoke(): bool
    {
        $this->revoked_at = now();
        return $this->save();
    }
}

