<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchResultRepository")
 */
class SearchResult
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SearchRequest")
     * @var SearchRequest
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Hotel")
     * @var Hotel
     */
    private $hotel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meal")
     * @var Meal
     */
    private $meal;

    /**
     * @ORM\Embedded(class="App\Entity\Money")
     * @var Money
     */
    private $price;

    /**
     * @ORM\Column(length=255)
     * @var string
     */
    private $roomName;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SearchRequest
     */
    public function getRequest(): SearchRequest
    {
        return $this->request;
    }

    /**
     * @param SearchRequest $request
     * @return SearchResult
     */
    public function setRequest(SearchRequest $request): SearchResult
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return SearchResult
     */
    public function setHotel(Hotel $hotel): SearchResult
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return Meal
     */
    public function getMeal(): Meal
    {
        return $this->meal;
    }

    /**
     * @param Meal $meal
     * @return SearchResult
     */
    public function setMeal(Meal $meal): SearchResult
    {
        $this->meal = $meal;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoomName(): string
    {
        return $this->roomName;
    }

    /**
     * @param string $roomName
     * @return SearchResult
     */
    public function setRoomName(string $roomName): SearchResult
    {
        $this->roomName = $roomName;
        return $this;
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @param Money $price
     * @return SearchResult
     */
    public function setPrice(Money $price): SearchResult
    {
        $this->price = $price;
        return $this;
    }
}
