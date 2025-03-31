<?php

namespace App\Traits;

trait HasLikes
{
    /**
     * Boot the trait.
     */
    public static function bootHasLikes()
    {
        static::deleting(function ($model) {
            $model->likes()->delete();
        });
    }

    /**
     * Relationship for likes.
     */
    public function likes()
    {
        return $this->morphMany(\App\Models\Like::class, 'likeable');
    }

    /**
     * Like the model (toggle behavior).
     */
    public function like()
    {
        $ip = request()->ip();
        $existingVote = $this->likes()->where('ip_address', $ip)->first();

        if ($existingVote) {
            if ($existingVote->is_like) {
                // Already liked - remove the like
                return $existingVote->delete();
            } else {
                // Currently disliked - change to like
                return $existingVote->update(['is_like' => true]);
            }
        }

        // No existing vote - create new like
        return $this->likes()->create([
            'ip_address' => $ip,
            'is_like' => true,
        ]);
    }

    /**
     * Dislike the model (toggle behavior).
     */
    public function dislike()
    {
        $ip = request()->ip();
        $existingVote = $this->likes()->where('ip_address', $ip)->first();

        if ($existingVote) {
            if (!$existingVote->is_like) {
                // Already disliked - remove the dislike
                return $existingVote->delete();
            } else {
                // Currently liked - change to dislike
                return $existingVote->update(['is_like' => false]);
            }
        }

        // No existing vote - create new dislike
        return $this->likes()->create([
            'ip_address' => $ip,
            'is_like' => false,
        ]);
    }

    /**
     * Get the like count.
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->where('is_like', true)->count();
    }

    /**
     * Get the dislike count.
     */
    public function getDislikesCountAttribute()
    {
        return $this->likes()->where('is_like', false)->count();
    }

    /**
     * Check if the current IP has liked.
     */
    public function getHasLikedAttribute()
    {
        if (!request()->ip()) {
            return false;
        }

        return $this->likes()
            ->where('ip_address', request()->ip())
            ->where('is_like', true)
            ->exists();
    }

    /**
     * Check if the current IP has disliked.
     */
    public function getHasDislikedAttribute()
    {
        if (!request()->ip()) {
            return false;
        }

        return $this->likes()
            ->where('ip_address', request()->ip())
            ->where('is_like', false)
            ->exists();
    }

    /**
     * Get the current IP's vote status.
     * Returns: 'liked', 'disliked', or null
     */
    public function getCurrentVoteAttribute()
    {
        if (!request()->ip()) {
            return null;
        }

        $vote = $this->likes()
            ->where('ip_address', request()->ip())
            ->first();

        if (!$vote) {
            return null;
        }

        return $vote->is_like ? 'liked' : 'disliked';
    }

    public function scopeWithLikeCounts($query, array $conditions = [])
    {
        return $query->withCount([
            'likes as likes_count' => function($q) use ($conditions) {
                $q->where('is_like', true);
                $this->applyConditions($q, $conditions);
            },
            'likes as dislikes_count' => function($q) use ($conditions) {
                $q->where('is_like', false);
                $this->applyConditions($q, $conditions);
            }
        ]);
    }

    /**
     * Apply conditions to the query
     */
    protected function applyConditions($query, array $conditions)
    {
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }
    }

    /**
     * Get items with like counts in a date range
     */
    public function scopeWithLikeCountsBetweenDates($query, $startDate, $endDate)
    {
        return $query->withCount([
            'likes as likes_count' => function($q) use ($startDate, $endDate) {
                $q->where('is_like', true)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            },
            'likes as dislikes_count' => function($q) use ($startDate, $endDate) {
                $q->where('is_like', false)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }
        ]);
    }

    /**
     * Get items with like counts from specific IPs
     */
    public function scopeWithLikeCountsFromIps($query, array $ips)
    {
        return $query->withCount([
            'likes as likes_count' => function($q) use ($ips) {
                $q->where('is_like', true)
                  ->whereIn('ip_address', $ips);
            },
            'likes as dislikes_count' => function($q) use ($ips) {
                $q->where('is_like', false)
                  ->whereIn('ip_address', $ips);
            }
        ]);
    }
}
