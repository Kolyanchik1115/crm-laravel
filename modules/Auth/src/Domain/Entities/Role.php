<?php

declare(strict_types=1);

namespace Modules\Auth\src\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Auth\src\Domain\Enums\RoleName;
use Modules\Auth\src\Infrastructure\Database\Factories\RoleFactory;

/**
 * @property int $id
 * @property RoleName|string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['name'];

    protected $casts = [
        'name' => RoleName::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function getAuthority(): string
    {
        return 'ROLE_' . $this->name->value;
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
