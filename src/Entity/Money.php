<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Money
 * @package App\Entity
 * @ORM\Embeddable()
 */
class Money
{
    /**
     * @ORM\Column(type="decimal",scale=2,precision=10)
     * @var string
     */
    private $amount;

    /**
     * @ORM\Column(length=3)
     * @var string
     */
    private $currency;

    /**
     * Money constructor.
     * @param string $amount
     * @param string $currency
     */
    public function __construct(string $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}