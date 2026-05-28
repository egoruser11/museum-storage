<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactSubmission extends Model
{
    use HasFactory;

    public const ACTION_DONATE = 'donate';

    public const ACTION_SELL = 'sell';

    public const ACTIONS = [
        self::ACTION_DONATE => 'Передать в дар',
        self::ACTION_SELL => 'Продать музею',
    ];

    public const STATUS_NEW = 'new';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_NEW => 'Новая',
        self::STATUS_IN_REVIEW => 'На рассмотрении',
        self::STATUS_ACCEPTED => 'Принята',
        self::STATUS_REJECTED => 'Отклонена',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'artifact_id',
        'reviewed_by',
        'title',
        'owner_name',
        'contact_email',
        'contact_phone',
        'desired_action',
        'desired_price',
        'description',
        'provenance',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'desired_price' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->desired_action] ?? $this->desired_action;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
