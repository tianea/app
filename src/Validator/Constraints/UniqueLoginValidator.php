<?php
/**
 * Unique login validator.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Unique login validator class.
 */
class UniqueLoginValidator extends ConstraintValidator
{
    /**
     * Validate for login uniqueness.
     *
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint->userRepository) {
            return;
        }
        $result = $constraint->userRepository->findForUsernameUniqueness(
            $value,
            $constraint->userId
        );
        if ($result && count($result)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%login%', $value)
                ->addViolation();
        }
    }
}
