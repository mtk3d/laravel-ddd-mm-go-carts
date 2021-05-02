<?php

declare(strict_types=1);

namespace App\Track;

use App\Shared\Common\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'slots',
        'is_available'
    ];

    protected $casts = [
        'slots' => 'integer',
        'is_available' => 'boolean'
    ];

    public function __construct(array $attributes = [])
    {
        if (!isset($attributes['uuid'])) {
            $attributes['uuid'] = UUID::random()->toString();
        }

        parent::__construct($attributes);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TrackReservation::class);
    }
}
