<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'order_id',
        'order_reference',
        'user_id',
        'email',
        'shipping',
        'customer_name',
        'purchase_date',
        'items', // JSON array of items
        'total',
        'payment',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
    ];

    protected $casts = [
        'items' => 'array', // automatically cast items to array
        'shipping' => 'array',
        'purchase_date' => 'datetime',
    ];

    public function canTransitionTo(string $toStatus): bool
    {
        $fromStatus = (string) ($this->status ?? self::STATUS_PENDING);

        if ($fromStatus === $toStatus) {
            return false;
        }

        return match ($fromStatus) {
            self::STATUS_PENDING => in_array($toStatus, [self::STATUS_PAID, self::STATUS_FAILED, self::STATUS_CANCELLED], true),
            self::STATUS_FAILED => false,
            self::STATUS_PAID => false,
            self::STATUS_CANCELLED => false,
            default => false,
        };
    }
}
