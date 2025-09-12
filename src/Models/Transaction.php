<?php

namespace Tripay\PPOB\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'tripay_transactions';

    protected $fillable = [
        'tripay_trx_id',
        'api_trx_id',
        'product_id',
        'customer_number',
        'customer_name',
        'amount',
        'admin_fee',
        'total_amount',
        'profit',
        'status',
        'type', // prepaid or postpaid
        'message',
        'sn', // serial number
        'response_data',
        'webhook_data',
        'processed_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'profit' => 'decimal:2',
        'response_data' => 'array',
        'webhook_data' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_PREPAID = 'prepaid';
    const TYPE_POSTPAID = 'postpaid';

    /**
     * Get the product that belongs to the transaction
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Scope for successful transactions
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing transactions
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for prepaid transactions
     */
    public function scopePrepaid($query)
    {
        return $query->where('type', self::TYPE_PREPAID);
    }

    /**
     * Scope for postpaid transactions
     */
    public function scopePostpaid($query)
    {
        return $query->where('type', self::TYPE_POSTPAID);
    }

    /**
     * Scope for today's transactions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Scope for this month's transactions
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'api_trx_id';
    }

    /**
     * Accessor for formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Accessor for formatted total amount
     */
    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Get status badge for Backpack
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_SUCCESS => '<span class="badge badge-success">Success</span>',
            self::STATUS_PENDING => '<span class="badge badge-warning">Pending</span>',
            self::STATUS_PROCESSING => '<span class="badge badge-info">Processing</span>',
            self::STATUS_FAILED => '<span class="badge badge-danger">Failed</span>',
            self::STATUS_CANCELLED => '<span class="badge badge-secondary">Cancelled</span>',
            default => '<span class="badge badge-light">Unknown</span>',
        };
    }

    /**
     * Get type badge for Backpack
     */
    public function getTypeBadgeAttribute()
    {
        return $this->type === self::TYPE_PREPAID
            ? '<span class="badge badge-primary">Prepaid</span>'
            : '<span class="badge badge-info">Postpaid</span>';
    }

    /**
     * Accessor for customer info display
     */
    public function getCustomerInfoAttribute()
    {
        $info = $this->customer_number;
        if ($this->customer_name) {
            $info .= '<br><small>' . $this->customer_name . '</small>';
        }
        return $info;
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Mark transaction as success
     */
    public function markAsSuccess(array $data = []): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'completed_at' => now(),
            'response_data' => array_merge($this->response_data ?? [], $data),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $message = '', array $data = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'message' => $message ?: $this->message,
            'response_data' => array_merge($this->response_data ?? [], $data),
        ]);
    }

    /**
     * Mark transaction as processing
     */
    public function markAsProcessing(array $data = []): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
            'response_data' => array_merge($this->response_data ?? [], $data),
        ]);
    }
}