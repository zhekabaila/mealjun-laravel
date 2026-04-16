<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'flavor',
        'description',
        'price',
        'image_url',
        'shopee_link',
        'tiktok_link',
        'whatsapp_link',
        'stock_status',
        'view_count',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Get the user who created this product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all visitor analytics for this product.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(VisitorAnalytic::class, 'product_id');
    }

    /**
     * Get all generated captions for this product.
     */
    public function generatedCaptions(): HasMany
    {
        return $this->hasMany(GeneratedCaption::class, 'product_id');
    }
}
