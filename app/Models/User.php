<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Konstansok a szerepkörökhöz, hogy a kódban ne szöveges literálok szerepeljenek (Backend Schema 5.).
    // Role constants so the code does not rely on string literals (Backend Schema 5.).
    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * A role és a privacy_accepted_at szándékosan nincs itt: a kód állítja be őket,
     * így űrlapon senki nem adhat magának admin szerepet (Backend Schema 5.).
     * role and privacy_accepted_at are intentionally missing: the code sets them,
     * so nobody can grant themselves the admin role through a form (Backend Schema 5.).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
    ];

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    // Egyetlen helyen dől el, ki admin; a middleware és a policy is ezt hívja (Backend Schema 10.).
    // Admin status is decided in one place; both the middleware and the policy call this (Backend Schema 10.).
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
