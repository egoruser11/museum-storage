<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NEW => 'Новая',
        self::STATUS_APPROVED => 'Одобрена',
        self::STATUS_PAID => 'Оплачена',
        self::STATUS_REJECTED => 'Отклонена',
        self::STATUS_CANCELLED => 'Отменена',
    ];

    protected $fillable = [
        'user_id',
        'artifact_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'offered_price',
        'status',
        'comment',
        'admin_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'offered_price' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
