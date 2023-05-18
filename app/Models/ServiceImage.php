<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceImage extends Model
{
    use HasFactory;

    /**
     * Get the service that owns the ServiceImage
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
