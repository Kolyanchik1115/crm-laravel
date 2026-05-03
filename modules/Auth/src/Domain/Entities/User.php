<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\src\Infrastructure\Database\Factories\UserFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string|null $delivery_address
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @method static \Modules\Auth\src\Infrastructure\Database\Factories\UserFactory factory()
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value)
 * @method static self|null find(int $id)
 *
 * @method bool hasRole(string $role)
 * @method static int countByRole(string $roleName)
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'delivery_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['roles'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps();
    }

    public function getAuthorities(): array
    {
        return $this->roles->map(function ($role) {
            return $role->getAuthority();
        })->toArray();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name')->toArray(),
            'authorities' => $this->getAuthorities(),
        ];
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function isEnabled(): bool
    {
        return !$this->trashed();
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
