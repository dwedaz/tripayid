<?php

namespace Tripay\PPOB\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'tripay_operators';

    protected $fillable = [
        'operator_id',
        'operator_name',
        'operator_code',
        'description',
        'status',
        'type', // prepaid or postpaid
        'logo_url',
        'sort_order',
        'synced_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * Get all products for this operator
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'operator_id', 'operator_id');
    }

    /**
     * Get active products only
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('status', true);
    }

    /**
     * Scope for active operators
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for prepaid operators
     */
    public function scopePrepaid($query)
    {
        return $query->where('type', 'prepaid');
    }

    /**
     * Scope for postpaid operators
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
        return 'operator_id';
    }

    /**
     * Mutator for operator_name - capitalize first letter
     */
    public function setOperatorNameAttribute($value)
    {
        $this->attributes['operator_name'] = strtoupper($value);
    }

    /**
     * Accessor for display name in admin panel
     */
    public function getDisplayNameAttribute()
    {
        return $this->operator_name . ' (' . ucfirst($this->type) . ')';
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

    /**
     * Get logo image attribute for Backpack
     */
    public function getLogoImageAttribute()
    {
        if ($this->logo_url) {
            return '<img src="' . $this->logo_url . '" alt="' . $this->operator_name . '" style="max-width: 50px; max-height: 50px;">';
        }
        return '-';
    }
}