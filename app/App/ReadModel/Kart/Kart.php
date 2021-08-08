<?php

declare(strict_types=1);

namespace Karting\App\ReadModel\Kart;

use Illuminate\Database\Eloquent\Model;
use Karting\App\ReadModel\ResourceReservation\ResourceReservation;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kart extends Model
{
    protected $table = 'karts_read_model';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'enabled'
    ];

    protected $casts = [
        'enabled' => 'boolean'
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(ResourceReservation::class, 'resource_item_id', 'uuid');
    }
}
