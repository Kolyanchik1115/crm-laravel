<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
