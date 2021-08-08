<?php

declare(strict_types=1);


namespace Karting\Availability\Domain;

use Karting\Shared\Common\UUID;
use Karting\Shared\ReservationId;
use Karting\Shared\ResourceId;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'uuid',
        'from',
        'to',
        'resource_item_id',
        'reservation_id',
    ];

    public static function of(CarbonPeriod $period, ResourceId $resourceId, ReservationId $reservationId): Reservation
    {
        return new Reservation([
            'uuid' => UUID::random()->toString(),
            'from' => $period->getStartDate()->toDateTimeString(),
            'to' => $period->getEndDate()->toDateTimeString(),
            'resource_item_id' => $resourceId->id()->toString(),
            'reservation_id' => $reservationId->id()->toString()
        ]);
    }

    public function id(): UUID
    {
        return new UUID($this->attributes['uuid']);
    }

    public function period(): CarbonPeriod
    {
        return new CarbonPeriod($this->attributes['from'], $this->attributes['to']);
    }
}
