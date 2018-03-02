<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class StayDateRangeValidator
 * @package App\Validator\Constraints
 */
class StayDateRangeValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint StayDateRange */
        if (
            !method_exists($value, $constraint->startDate) ||
            !method_exists($value, $constraint->endDate)
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class %s must implements methods %s and %s',
                    get_class($value),
                    $constraint->startDate,
                    $constraint->endDate
                )
            );
        }
        /** @var \DateTime $startDate */
        $startDate = call_user_func([$value, $constraint->startDate]);
        /** @var \DateTime $endDate */
        $endDate = call_user_func([$value, $constraint->endDate]);
        if (!$startDate instanceof \DateTime || !$endDate instanceof \DateTime) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
            return;
        }
        $startDate = clone $startDate;
        $endDate = clone $endDate;
        $today = new \DateTime();
        $today->setTime(0, 0);
        $startDate->setTime(0, 0);
        $endDate->setTime(0, 0);
        if ($endDate <= $startDate || $startDate < $today) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
