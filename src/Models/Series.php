<?php

namespace ClarionApp\RssTorrents\Models;

use ClarionApp\EloquentMultiChainBridge\EloquentMultiChainBridge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use EloquentMultiChainBridge, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rss_torrents_series';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'title',
        'feed_url',
        'subscribed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subscribed' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Scope a query to only include subscribed series.
     */
    public function scopeSubscribed($query)
    {
        return $query->where('subscribed', true);
    }

    /**
     * Scope a query to only include unsubscribed series.
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('subscribed', false);
    }

    /**
     * Get the formatted name attribute.
     */
    public function getFormattedNameAttribute(): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    /**
     * Get the episodes for the series.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }
}
