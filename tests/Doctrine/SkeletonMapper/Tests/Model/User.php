<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

class User implements HydratableInterface, PersistableInterface
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
     * @see HydratableInterface
     *
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

    /**
     * @see PersistableInterface
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '_id' => (int) $this->id,
            'username' => $this->username,
            'password' => $this->password,
        );
    }
}
