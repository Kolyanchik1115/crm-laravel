<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Invoice\src\Infrastructure\Database\Factories\InvoiceFactory;
use Modules\Service\src\Domain\Entities\Service;

/**
 * @property int $id
 * @property int $client_id
 * @property string $invoice_number
 * @property float $total_amount
 * @property string $status
 * @property \DateTimeInterface|null $issued_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItem> $items
 * @method static \Modules\Invoice\src\Infrastructure\Database\Factories\InvoiceFactory factory()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDate(string $column, string $date)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice count()
 * @method static mixed max(string $column)
 * @method static mixed sum(string $column)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereIn(string $column, array $values)
 */
class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'client_id',
        'invoice_number',
        'total_amount',
        'status',
        'issued_at',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'issued_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
    // belongsToMany returns Services models instead of InvoiceItem,
    // so nested items.service fails and pivot fields
    // (quantity, unit_price) are not accessible properly
    //    public function items(): BelongsToMany
    //    {
    //        return $this->belongsToMany(Services::class, 'invoice_items')
    //            ->withPivot('quantity', 'unit_price');
    //    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'invoice_items')
            ->using(InvoiceItem::class)
            ->withPivot('quantity', 'unit_price');
    }

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }
}
