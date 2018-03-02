<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchRequestRepository")
 * @AppAssert\StayDateRange(startDate="getCheckIn", endDate="getCheckOut")
 */
class SearchRequest
{
    const STATUS_NEW = 0;
    const STATUS_COMPLETE = 1;
    const STATUS_OLD = 2;
    const STATUS_ERROR = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="City")
     * @var City
     */
    private $city;

    /**
     * @Assert\Date()
     * @Assert\NotBlank()
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    private $checkIn;

    /**
     * @Assert\Date()
     * @Assert\NotBlank()
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    private $checkOut;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $adults = 1;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $status = self::STATUS_NEW;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->checkIn = new \DateTime('+ 2 day');
        $this->checkOut = new \DateTime('+ 9 day');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param City $city
     * @return SearchRequest
     */
    public function setCity(City $city): SearchRequest
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCheckIn(): ?\DateTime
    {
        return $this->checkIn;
    }

    /**
     * @param \DateTime $checkIn
     * @return SearchRequest
     */
    public function setCheckIn(\DateTime $checkIn): SearchRequest
    {
        $this->checkIn = $checkIn;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCheckOut(): ?\DateTime
    {
        return $this->checkOut;
    }

    /**
     * @param \DateTime $checkOut
     * @return SearchRequest
     */
    public function setCheckOut(\DateTime $checkOut): SearchRequest
    {
        $this->checkOut = $checkOut;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): ?int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return SearchRequest
     */
    public function setAdults(int $adults): SearchRequest
    {
        $this->adults = $adults;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return SearchRequest
     */
    public function setStatus(int $status): SearchRequest
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETE, self::STATUS_OLD]);
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->status == self::STATUS_ERROR;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->status == self::STATUS_NEW;
    }
}
