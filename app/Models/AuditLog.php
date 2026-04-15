<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $invoice_id
 * @property string $event_type
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property array|null $payload
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Invoice|null $invoice
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AuditLog updateOrCreate(array $attributes, array $values)
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'invoice_id',
        'event_type',
        'entity_type',
        'entity_id',
        'payload',
        'user_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
