<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $client_id
 * @property string $account_number
 * @property float $balance
 * @property string $currency
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|Transaction[] $transactions
 *
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
}
