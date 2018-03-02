<?php

namespace App\DataTransferObject;

/**
 * Class SearchResultRoom
 * @package App\DataTransferObject
 */
class SearchResultRoom
{
    /**
     * @var int
     */
    public $roomNumber;

    /**
     * @var string
     */
    public $roomName;

    /**
     * @var int
     */
    public $roomTypeId;

    /**
     * @var int
     */
    public $roomCategoryId;

    /**
     * @var int
     */
    public $roomViewId;

    /**
     * @var int
     */
    public $children;

    /**
     * @var array
     */
    public $ages = array();

    /**
     * @var int
     */
    public $cots;

    /**
     * @var string
     */
    public $providerData;
}