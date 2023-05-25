<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Service extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'price_min',
        'price_max',
        'location',
        'location_lat',
        'location_long'
    ];

    /**
     * Get the user that owns the Service
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The categories that belong to the Service
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'service_categories');
    }

    /**
     * Get all of the images for the Service
     */
    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class);
    }

    /**
     * Get all of the serviceRequests for the Service
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Get all of the reviews for the Service
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class);
    }

    /**
     * Get all of the bookmarked for the Service
     */
    public function bookmarked(): HasMany
    {
        return $this->hasMany(ServiceBookmark::class);
    }
}
