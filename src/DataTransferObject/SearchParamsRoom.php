<?php

namespace App\DataTransferObject;

/**
 * Class SearchParamsRoom
 * @package App\DataTransferObject
 */
class SearchParamsRoom
{
    /**
     * @var int
     */
    public $adults;
    /**
     * @var int
     */
    public $roomNumber;

    /**
     * SearchParamsRoom constructor.
     * @param int $adults
     * @param int $roomNumber
     */
    public function __construct(int $adults, int $roomNumber)
    {
        $this->adults = $adults;
        $this->roomNumber = $roomNumber;
    }
}