<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'api_key',
        'client_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // App\Models\Marketplace
    public function apiKey()
    {
        return $this->hasOne(MarketplaceApiKey::class, 'client_id', 'client_id');
    }

}
