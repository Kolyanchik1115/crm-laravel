<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    // belongsToMany returns Service models instead of InvoiceItem,
    // so nested items.service fails and pivot fields
    // (quantity, unit_price) are not accessible properly
    //    public function items(): BelongsToMany
    //    {
    //        return $this->belongsToMany(Service::class, 'invoice_items')
    //            ->withPivot('quantity', 'unit_price');
    //    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'invoice_items')
            ->using(InvoiceItem::class)
            ->withPivot('quantity', 'unit_price');
    }
}
