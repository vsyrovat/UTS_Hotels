<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StayDateRange extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Date range of stay is incorrect';
    /**
     * Name of method to fetch first date of range
     *
     * @var string
     */
    public $startDate;
    /**
     * Name of method to fetch last date of range
     *
     * @var
     */
    public $endDate;

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['startDate', 'endDate'];
    }
}
