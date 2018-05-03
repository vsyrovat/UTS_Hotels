<?php declare(strict_types=1);

namespace App\Entity\Virtual;

use App\Entity\Hotel;
use App\Entity\Money;
use App\Entity\SearchRequest;
use App\Entity\SearchResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;

class CustomSearchResult
{
    /* @var SearchRequest */
    private $request;

    /* @var Hotel */
    private $hotel;

    /* @var Money */
    private $minPrice;

    /* @var SearchResult[] */
    private $searchResults;

    public function __construct()
    {
        $this->searchResults = new ArrayCollection();
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
     * @return self
     */
    public function setRequest(SearchRequest $request): self
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
     * @return self
     */
    public function setHotel(Hotel $hotel): self
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return Money
     */
    public function getMinPrice(): Money
    {
        return $this->minPrice;
    }

    /**
     * @param string $minPrice
     * @return self
     */
    public function setMinPrice(Money $minPrice): self
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    /**
     * @return ArrayCollection|SearchResult[]
     */
    public function getSearchResults(): array
    {
        return $this->searchResults;
    }

    /**
     * @param SearchResult[]|ArrayCollection|PersistentCollection|Collection $searchResults
     * @return self
     */
    public function setSearchResults($searchResults): self
    {
        $this->searchResults = $searchResults;
        return $this;
    }
}
