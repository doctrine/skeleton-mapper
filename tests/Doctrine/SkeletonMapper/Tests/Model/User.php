<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;

class User implements HydratableInterface
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
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        $this->called[] = $method;
    }

    /**
     * @param array $data
     */
    public function hydrate(array $data)
    {
        if (isset($data['_id'])) {
            $this->id = (int) $data['_id'];
        }

        if (isset($data['username'])) {
            $this->username = (string) $data['username'];
        }

        if (isset($data['password'])) {
            $this->password = (string) $data['password'];
        }
    }
}
