<?php

namespace Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User;

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
