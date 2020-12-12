<?php

declare(strict_types=1);

namespace App\Availability\Domain;

use App\Shared\Common\Result;
use App\Shared\Common\UuidsTrait;
use App\Shared\ResourceId;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ResourceItem extends Model
{
    use UuidsTrait;

    protected $with = ['reservations'];

    protected $fillable = [
        'id',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean'
    ];

    protected $attributes = [
        'is_available' => true
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->relations['reservations'] = new EloquentCollection();
    }

    public static function of(ResourceId $id, bool $available = true): ResourceItem
    {
        return new ResourceItem([
            'id' => (string)$id->id(),
            'is_available' => $available
        ]);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reserve(CarbonPeriod $period): Result
    {
        if (!$this->isAvailable()) {
            return Result::failure('ResourceItem unavailable');
        }

        if (!$this->isAvailableIn($period)) {
            return Result::failure('Cannot reserve in this period');
        }

        $reservation = Reservation::of($period, $this->getId());
        $this->relations['reservations']->add($reservation);

        $events = new Collection([
            ResourceReserved::newOne($this->getId(), $period)
        ]);

        return Result::success($events);
    }

    public function withdraw(): Result
    {
        if (!$this->isAvailable()) {
            return Result::failure('ResourceItem already withdrawn');
        }

        $this->attributes['is_available'] = false;

        $events = new Collection([
            ResourceWithdrawn::newOne($this->getId())
        ]);

        return Result::success($events);
    }

    public function turnOn(): Result
    {
        if ($this->isAvailable()) {
            return Result::failure('ResourceItem already turned on');
        }

        $this->attributes['is_available'] = true;

        $events = new Collection([
            ResourceTurnedOn::newOne($this->getId())
        ]);

        return Result::success($events);
    }

    public function getId(): ResourceId
    {
        return ResourceId::of($this->attributes['id']);
    }

    private function isAvailable(): bool
    {
        return (bool)$this->attributes['is_available'];
    }

    private function isAvailableIn(CarbonPeriod $period): bool
    {
        return !$this->relations['reservations']
            ->map(fn (Reservation $reservation): CarbonPeriod => $reservation->getPeriod())
            ->filter(fn (CarbonPeriod $p): bool => $p->overlaps($period))
            ->count();
    }
}
