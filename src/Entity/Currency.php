<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyRepository")
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\Column(length=3)
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $rate;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }
}
