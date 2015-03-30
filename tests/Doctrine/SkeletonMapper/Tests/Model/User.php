<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

class User implements HydratableInterface, PersistableInterface, NotifyPropertyChanged
{
    /**
     * @var array
     */
    private $listeners = array();

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    public $called;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $id = (int) $id;

        if ($this->id !== $id) {
            $this->onPropertyChanged('id', $this->id, $id);
            $this->id = $id;
        }
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $username = (string) $username;

        if ($this->username !== $username) {
            $this->onPropertyChanged('username', $this->username, $username);
            $this->username = $username;
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $password = (string) $password;

        if ($this->password !== $password) {
            $this->onPropertyChanged('password', $this->password, $password);
            $this->password = $password;
        }
    }

    /**
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        $this->called[] = $method;
    }

    /**
     * @param \Doctrine\Common\PropertyChangedListener $listener
     */
    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->listeners[] = $listener;
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

    /**
     * @param string $propName
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    protected function onPropertyChanged($propName, $oldValue, $newValue)
    {
        if ($this->listeners) {
            foreach ($this->listeners as $listener) {
                $listener->propertyChanged($this, $propName, $oldValue, $newValue);
            }
        }
    }
}
