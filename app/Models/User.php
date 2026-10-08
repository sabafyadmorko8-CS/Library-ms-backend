<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens; // Temporarily commented out

class User extends Authenticatable
{
    use HasFactory, Notifiable; // Removed HasApiTokens temporarily

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'approved_at',
        'approved_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'approved_at' => 'datetime',
    ];

    /**
     * User has many borrows
     */
    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    /**
     * User who approved this user
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Users approved by this user
     */
    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Temporary method to create tokens (will be replaced with Sanctum)
     */
    public function createToken(string $name)
    {
        // For now, return a simple token structure
        return (object) [
            'plainTextToken' => 'temp_token_' . $this->id . '_' . time()
        ];
    }
}
