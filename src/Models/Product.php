<?php

namespace Tripay\PPOB\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'tripay_products';

    protected $fillable = [
        'id',
        'name',
        'code',
        'description',
        'category_id',
        'operator_id',
        'price',
        'status',
        'type',
        'synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the category that owns the product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * Get the operator that owns the product
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'operator_id');
    }

    /**
     * Get all transactions for this product
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'product_id', 'product_id');
    }

    /**
     * Get successful transactions only
     */
    public function successfulTransactions(): HasMany
    {
        return $this->transactions()->where('status', 'success');
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for prepaid products
     */
    public function scopePrepaid($query)
    {
        return $query->where('type', 'prepaid');
    }

    /**
     * Scope for postpaid products
     */
    public function scopePostpaid($query)
    {
        return $query->where('type', 'postpaid');
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for products by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for products by operator
     */
    public function scopeByOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'product_id';
    }

    /**
     * Accessor for display name in admin panel
     */
    public function getDisplayNameAttribute()
    {
        return $this->product_name . ' - Rp ' . number_format($this->product_price, 0, ',', '.');
    }

    /**
     * Accessor for formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->product_price, 0, ',', '.');
    }

    /**
     * Accessor for formatted selling price
     */
    public function getFormattedSellingPriceAttribute()
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    /**
     * Accessor for profit margin percentage
     */
    public function getProfitMarginPercentageAttribute()
    {
        if ($this->product_price > 0) {
            return round((($this->selling_price - $this->product_price) / $this->product_price) * 100, 2) . '%';
        }
        return '0%';
    }

    /**
     * Get status badge for Backpack
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status 
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>';
    }

    /**
     * Get type badge for Backpack
     */
    public function getTypeBadgeAttribute()
    {
        return $this->type === 'prepaid'
            ? '<span class="badge badge-primary">Prepaid</span>'
            : '<span class="badge badge-info">Postpaid</span>';
    }

    /**
     * Get transactions count attribute
     */
    public function getTransactionsCountAttribute()
    {
        return $this->transactions()->count();
    }

    /**
     * Get successful transactions count attribute
     */
    public function getSuccessfulTransactionsCountAttribute()
    {
        return $this->successfulTransactions()->count();
    }

    /**
     * Auto-calculate profit margin when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            if ($product->selling_price && $product->product_price) {
                $product->profit_margin = $product->selling_price - $product->product_price;
            }
        });
    }
}