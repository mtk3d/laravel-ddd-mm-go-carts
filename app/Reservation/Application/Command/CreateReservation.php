<?php

declare(strict_types=1);

namespace Karting\Reservation\Application\Command;

use Karting\Shared\Common\Command;
use Karting\Shared\ReservationId;
use Karting\Shared\ResourceId;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class CreateReservation implements Command
{
    private ReservationId $reservationId;
    /** @var Collection<int, ResourceId> */
    private Collection $kartsIds;
    private ResourceId $trackId;
    private CarbonPeriod $period;

    public function __construct(
        ReservationId $reservationId,
        Collection $kartsIds,
        ResourceId $trackId,
        CarbonPeriod $period
    ) {
        $this->reservationId = $reservationId;
        $this->kartsIds = $kartsIds;
        $this->trackId = $trackId;
        $this->period = $period;
    }

    public static function fromArray(array $payload): CreateReservation
    {
        return new CreateReservation(
            ReservationId::of($payload['uuid']),
            new Collection($payload['karts_ids']),
            ResourceId::of($payload['track_id']),
            new CarbonPeriod($payload['from'], $payload['to'])
        );
    }

    public function reservationId(): ReservationId
    {
        return $this->reservationId;
    }

    public function kartIds(): Collection
    {
        return $this->kartsIds;
    }

    public function trackId(): ResourceId
    {
        return $this->trackId;
    }

    public function period(): CarbonPeriod
    {
        return $this->period;
    }
}
