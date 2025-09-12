<?php

namespace Tripay\PPOB\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'tripay_categories';

    protected $fillable = [
        'category_id',
        'category_name',
        'description',
        'status',
        'type', // prepaid or postpaid
        'sort_order',
        'synced_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * Get all products in this category
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }

    /**
     * Get active products only
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('status', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for prepaid categories
     */
    public function scopePrepaid($query)
    {
        return $query->where('type', 'prepaid');
    }

    /**
     * Scope for postpaid categories
     */
    public function scopePostpaid($query)
    {
        return $query->where('type', 'postpaid');
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'category_id';
    }

    /**
     * Mutator for category_name - capitalize first letter
     */
    public function setCategoryNameAttribute($value)
    {
        $this->attributes['category_name'] = ucwords(strtolower($value));
    }

    /**
     * Accessor for display name in admin panel
     */
    public function getDisplayNameAttribute()
    {
        return $this->category_name . ' (' . ucfirst($this->type) . ')';
    }

    /**
     * Get products count attribute
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get active products count attribute
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->activeProducts()->count();
    }
}