<?php

declare(strict_types=1);

namespace Modules\Service\Domain\Entities;

use Database\Factories\InvoiceFactory;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Invoice\Domain\Entities\Invoice;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $base_price
 * @property string $currency
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Invoice[] $invoices
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Service find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Service findOrFail(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Service where(string $column, $value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service orderBy(string $column, string $direction = 'asc')
 */
class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items')
            ->withPivot('quantity', 'unit_price');
    }

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }
}
