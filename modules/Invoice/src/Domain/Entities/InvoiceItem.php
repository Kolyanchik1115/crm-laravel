<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Domain\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Service\src\Domain\Entities\Service;

/**
 * @property int $invoice_id
 * @property int $service_id
 * @property int $quantity
 * @property float $unit_price
 *
 * @property-read Invoice $invoice
 * @property-read Service $service
 *
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem create(array $attributes)
 */
//Pivot - for manyToMany relation table
class InvoiceItem extends Pivot
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'service_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
    ];

    public $timestamps = false;

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
