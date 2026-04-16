<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorAnalytic extends Model
{
    use HasUuids;

    protected $table = 'visitor_analytics';
    public $timestamps = false;

    protected $fillable = [
        'visit_date',
        'visitor_ip',
        'visitor_city',
        'visitor_province',
        'visitor_country',
        'page_viewed',
        'product_id',
        'referrer_url',
        'user_agent',
        'session_id',
        'created_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Get the product associated with this analytic.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
