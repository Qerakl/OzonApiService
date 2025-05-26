<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceForecast extends Model
{
    protected $fillable = [
        'marketplace_api_key_id',
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
