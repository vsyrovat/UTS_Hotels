<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpecialOfferRepository")
 */
class SpecialOffer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;
    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @var string
     */
    private $name;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @Assert\NotBlank()
     * @var Country
     */
    private $country;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @var City
     */
    private $city;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Hotel")
     * @var Hotel
     */
    private $hotel;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $isActive;
    /**
     * @Assert\NotBlank()
     * @ORM\Embedded(class="App\Entity\Discount")
     * @var Discount
     */
    private $discount;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     * @return SpecialOffer
     */
    public function setName(string $name): SpecialOffer
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return SpecialOffer
     */
    public function setCountry(Country $country): SpecialOffer
    {
        $this->country = $country;
        return $this;
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
     * @return SpecialOffer
     */
    public function setCity(City $city = null): SpecialOffer
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return SpecialOffer
     */
    public function setHotel(Hotel $hotel = null): SpecialOffer
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->isActive;
    }

    /**
     * @param bool $isActive
     * @return SpecialOffer
     */
    public function setIsActive(bool $isActive): SpecialOffer
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Discount
     */
    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    /**
     * @param Discount $discount
     * @return SpecialOffer
     */
    public function setDiscount(Discount $discount): SpecialOffer
    {
        $this->discount = $discount;
        return $this;
    }
}
