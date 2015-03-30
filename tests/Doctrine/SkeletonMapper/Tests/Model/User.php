<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

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

    /**
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        $this->called[] = $method;
    }
}
