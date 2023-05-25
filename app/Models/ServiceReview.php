<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReview extends Model
{
    use HasFactory;

    /**
     * Get the user that owns the ServiceReview
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service that owns the ServiceReview
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
