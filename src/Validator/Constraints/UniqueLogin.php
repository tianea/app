<?php
/**
 * Unique login.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Unique login constraint.
 */
class UniqueLogin extends Constraint
{
    /**
     * Error message.
     *
     * @var string $message
     */
    public $message = 'message.login_not_unique';

    /**
     * User ID.
     *
     * @var int|string|null $userId
     */
    public $userId = null;

    /**
     * User repository.
     *
     * @var null|\Repository\UserRepository $userRepository
     */
    public $userRepository = null;

}