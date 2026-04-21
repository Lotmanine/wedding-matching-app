<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\Wilaya;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'whatsapp',
        'wilaya_code',
        'status',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
            'wilaya_code'       => Wilaya::class,
            'last_active_at'    => 'datetime',
        ];
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function serviceRequestsWon(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'unlocked_by_user_id');
    }

    public function creditLedger(): HasMany
    {
        return $this->hasMany(CreditLedger::class);
    }

    public function providerServices(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function providerCoverage(): HasMany
    {
        return $this->hasMany(ProviderCoverage::class);
    }

    public function isCouple(): bool
    {
        return $this->role === UserRole::Couple;
    }

    public function isProvider(): bool
    {
        return $this->role === UserRole::Provider;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
