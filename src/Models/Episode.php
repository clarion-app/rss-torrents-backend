<?php

namespace ClarionApp\RssTorrents\Models;

use ClarionApp\EloquentMultiChainBridge\EloquentMultiChainBridge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Episode extends Model
{
    use EloquentMultiChainBridge, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rss_torrents_episodes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'series_id',
        'episode',
        'completed',
        'hash_string',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the series that owns the episode.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * Scope a query to only include completed episodes.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope a query to only include incomplete episodes.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    /**
     * Scope a query to only include episodes with hash strings.
     */
    public function scopeWithHash($query)
    {
        return $query->whereNotNull('hash_string');
    }

    /**
     * Check if the episode has a hash string.
     */
    public function hasHash(): bool
    {
        return !is_null($this->hash_string);
    }

    /**
     * Mark the episode as completed.
     */
    public function markCompleted(): bool
    {
        return $this->update(['completed' => true]);
    }

    /**
     * Mark the episode as incomplete.
     */
    public function markIncomplete(): bool
    {
        return $this->update(['completed' => false]);
    }
}
