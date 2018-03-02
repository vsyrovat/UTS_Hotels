<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Discount
 * @package App\Entity
 * @ORM\Embeddable()
 */
class Discount
{
    const DISCOUNT_TYPE_ABSOLUTE = 'a';
    const DISCOUNT_TYPE_MULTIPLIER = 'm';

    /**
     * @ORM\Column(length=1)
     * @var string
     */
    private $type = self::DISCOUNT_TYPE_MULTIPLIER;
    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $value;

    /**
     * Discount constructor.
     * @param string $type
     * @param int $value
     */
    public function __construct(string $type = null, int $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string)$this->type;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return (int)$this->value;
    }

    /**
     * @param string $type
     * @return Discount
     */
    public function setType(string $type): Discount
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $value
     * @return Discount
     */
    public function setValue(int $value): Discount
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return array
     */
    public static function getDiscountTypes()
    {
        return [
            'Percent' => self::DISCOUNT_TYPE_MULTIPLIER,
            'Rubles' => self::DISCOUNT_TYPE_ABSOLUTE
        ];
    }
}