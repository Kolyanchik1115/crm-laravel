<?php

declare(strict_types=1);

namespace Modules\Client\src\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Client\src\Infrastructure\Database\Factories\ClientFactory;
use Modules\Invoice\src\Domain\Entities\Invoice;

/**
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property string|null $password_hash
 * @property float $balance
 * @property string $currency
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Account> $accounts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices
 * @method static \Illuminate\Database\Eloquent\Builder|Client sum(string $column)
 * @method static \Illuminate\Database\Eloquent\Builder|Client orderBy(string $column, string $direction = 'asc')
 * @method static \Modules\Client\src\Infrastructure\Database\Factories\ClientFactory factory()
 * @method static \Illuminate\Database\Eloquent\Builder|Client where(string $column, $value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereDate(string $column, string $date)
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, array $values)
 * @method static \Illuminate\Database\Eloquent\Builder|Client find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Client findOrFail(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Client create(array $attributes)
 * @method static int count()
 */
class Client extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'balance',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Relationships
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }
}
