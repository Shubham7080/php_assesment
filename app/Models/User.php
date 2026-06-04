<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isAuthor(): bool
    {
        return $this->role === UserRole::Author;
    }

    public function isReviewer(): bool
    {
        return $this->role === UserRole::Reviewer;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role->value];
    }
}
