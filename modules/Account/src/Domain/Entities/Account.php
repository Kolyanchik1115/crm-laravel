<?php

declare(strict_types=1);

namespace Modules\Account\src\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Account\src\Infrastructure\Database\Factories\AccountFactory;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Transaction\src\Domain\Entities\Transaction;

/**
 * @property int $id
 * @property int $client_id
 * @property string $account_number
 * @property float $balance
 * @property string $currency
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * /**
 * @method static \Modules\Account\src\Infrastructure\Database\Factories\AccountFactory factory()
 * @property-read \Modules\Client\src\Domain\Entities\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection $transactions
 * @method static \Modules\Account\src\Domain\Entities\Account create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|Account find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Account findOrFail(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Account lockForUpdate()
 */
class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'client_id',
        'account_number',
        'balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }
}
