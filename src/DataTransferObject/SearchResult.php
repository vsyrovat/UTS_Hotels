<?php

namespace App\DataTransferObject;

/**
 * Class SearchResult
 * @package App\DataTransferObject
 */
class SearchResult
{
    /**
     * @var int
     */
    public $hotelId;

    /**
     * @var int
     */
    public $providerId;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $information;

    /**
     * @var int
     */
    public $confirmation;

    /**
     * @var int
     */
    public $mealId;

    /**
     * @var int
     */
    public $mealBreakfastId;

    /**
     * @var SearchResultRoomList
     */
    public $rooms;

    /**
     * @var string
     */
    public $providerData;

    /**
     * @var string
     */
    public $providerSpecialOffer;

    /**
     * @var string
     */
    public $priceStatus;

    /**
     * @var string
     */
    public $priceBreakdownStatus;

    /**
     * @var bool
     */
    public $useNds;

    /**
     * @var bool
     */
    public $dynamicInventory;

    /**
     * @var \DateTime
     */
    public $checkIn;

    /**
     * @var int
     */
    public $duration;

    /**
     * @var string
     */
    public $userCurrency;

    /**
     * @var string
     */
    public $utsCompany;
}