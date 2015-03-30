<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

class User
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var array
     */
    public $called;

    public function __call($method, $arguments)
    {
        $this->called[] = $method;
    }
}
