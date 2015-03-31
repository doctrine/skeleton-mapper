<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
use Doctrine\SkeletonMapper\Persister\IdentifiableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

class User implements HydratableInterface, PersistableInterface, IdentifiableInterface, LoadMetadataInterface, NotifyPropertyChanged
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
    public $called = array();

    /**
     * Assign identifier to object.
     *
     * @param array $identifier
     */
    public function assignIdentifier(array $identifier)
    {
        $this->id = (int) $identifier['_id'];
    }

    public static function loadMetadata(ClassMetadataInterface $metadata)
    {
        $metadata->identifier = array('_id');
        $metadata->identifierFieldNames = array('id');
        $metadata->mapField(array(
            'name' => '_id',
            'fieldName' => 'id',
        ));
        $metadata->mapField(array(
            'fieldName' => 'username',
        ));
        $metadata->mapField(array(
            'fieldName' => 'password',
        ));
    }

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
     * @param array $changeSet
     *
     * @return array
     */
    public function prepareChangeSet(array $changeSet)
    {
        if ($changeSet) {
            $changeSet = array_map(function ($change) {
                return $change[1];
            }, $changeSet);

            $changeSet['_id'] = (int) $this->id;

            return $changeSet;
        }

        $changeSet = array(
            'username' => $this->username,
            'password' => $this->password,
        );

        if ($this->id !== null) {
            $changeSet['_id'] = (int) $this->id;
        }

        return $changeSet;
    }

    /**
     * @param string $propName
     * @param mixed  $oldValue
     * @param mixed  $newValue
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
