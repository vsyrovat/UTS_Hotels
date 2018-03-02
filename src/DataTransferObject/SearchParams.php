<?php

namespace App\DataTransferObject;

/**
 * Class SearchParams
 * @package App\DataTransferObject
 */
class SearchParams
{
    /**
     * @var int
     */
    public $cityId;
    /**
     * @var string
     */
    public $checkIn;
    /**
     * @var int
     */
    public $duration;
    /**
     * @var SearchParamsRoom[]
     */
    public $roomsByPax;

    /**
     * SearchParams constructor.
     * @param int $cityId
     * @param string $checkIn
     * @param int $duration
     * @param SearchParamsRoom[] $rooms
     */
    public function __construct(int $cityId, string $checkIn, int $duration, array $rooms)
    {
        $this->cityId = $cityId;
        $this->checkIn = $checkIn;
        $this->duration = $duration;
        $this->roomsByPax = $rooms;
    }
}