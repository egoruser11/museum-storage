<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artifact extends Model
{
    use HasFactory;

    public const STATUS_IN_STORAGE = 'in_storage';

    public const STATUS_ON_SALE = 'on_sale';

    public const STATUS_SOLD = 'sold';

    public const STATUS_RESTORATION = 'restoration';

    public const STATUSES = [
        self::STATUS_IN_STORAGE => 'В хранилище',
        self::STATUS_ON_SALE => 'Доступен для выкупа',
        self::STATUS_SOLD => 'Выкуплен',
        self::STATUS_RESTORATION => 'На реставрации',
    ];

    public const ACQUISITION_TYPES = [
        'donation' => 'Дарение',
        'purchase' => 'Выкуп музеем',
        'storage' => 'Фондовое хранение',
    ];

    protected $fillable = [
        'category_id',
        'owner_id',
        'title',
        'inventory_number',
        'period',
        'material',
        'condition_state',
        'acquisition_type',
        'status',
        'appraised_value',
        'sale_price',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'appraised_value' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function acquisitionLabel(): string
    {
        return self::ACQUISITION_TYPES[$this->acquisition_type] ?? $this->acquisition_type;
    }

    public function isAvailableForPurchase(): bool
    {
        return $this->status === self::STATUS_ON_SALE && $this->sale_price !== null;
    }

    public static function nextInventoryNumber(): string
    {
        $prefix = 'MS-'.now()->format('Y').'-';
        $maxSuffix = self::query()
            ->where('inventory_number', 'like', $prefix.'%')
            ->pluck('inventory_number')
            ->reduce(function (int $max, string $inventoryNumber) use ($prefix): int {
                $pattern = '/^'.preg_quote($prefix, '/').'(\d+)$/';

                if (preg_match($pattern, $inventoryNumber, $matches)) {
                    return max($max, (int) $matches[1]);
                }

                return $max;
            }, 0);

        $next = $maxSuffix + 1;

        do {
            $candidate = $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (self::query()->where('inventory_number', $candidate)->exists());

        return $candidate;
    }
}
