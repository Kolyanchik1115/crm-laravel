<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Account\src\Domain\Entities\Account;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Transaction\src\Infrastructure\Database\Factories\TransactionFactory;

/**
 * @property int $id
 * @property int $account_id
 * @property float $amount
 * @property string $type
 * @property string $status
 * @property string|null $description
 * @property string|null $confirmation_sent_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Account $account
 * @property-read Client|null $client
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDate(string $column, string $date)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction sum(string $column)
 */
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'account_id',
        'amount',
        'type',
        'status',
        'description',
        'confirmation_sent_at',
        'id',
    ];

    protected $casts = [
        'amount' => 'float',
        'confirmation_sent_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected static function newFactory(): TransactionFactory
    {
        return TransactionFactory::new();
    }
}
