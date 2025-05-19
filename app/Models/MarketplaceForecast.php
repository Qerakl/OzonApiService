<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceForecast extends Model
{
    protected $fillable = [
        'marketplace_api_key_id',
        'article',
        'name',
        'current_stock',
        'forecast',
        'recommendations',
    ];

    public function marketplaceApiKey()
    {
        return $this->belongsTo(MarketplaceApiKey::class);
    }
}
