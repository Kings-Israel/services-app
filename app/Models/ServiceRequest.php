<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['service_id', 'user_id', 'status'];

    /**
     * Get the user that owns the ServiceRequest
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service that owns the ServiceRequest
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
