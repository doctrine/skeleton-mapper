<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

class User extends BaseObject
{
    /**
     * @var id
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
     * @var \Doctrine\SkeletonMapper\Tests\Model\Profile
     */
    private $profile;

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
        $metadata->mapField(array(
            'name' => 'profileId',
            'fieldName' => 'profile',
        ));
    }

    public function getId()
    {
        return (int) $this->id;
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

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile)
    {
        if ($this->profile !== $profile) {
            $this->onPropertyChanged('profile', $this->profile, $profile);
            $this->profile = $profile;
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
     * @see HydratableInterface
     *
     * @param array                                           $data
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     */
    public function hydrate(array $data, ObjectManagerInterface $objectManager)
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

        if (isset($data['profileId'])) {
            $this->profile = $objectManager->find(
                'Doctrine\SkeletonMapper\Tests\Model\Profile',
                $data['profileId']
            );
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

            if (isset($changeSet['profile'])) {
                $changeSet['profileId'] = $changeSet['profile']->getId();
                unset($changeSet['profile']);
            }

            return $changeSet;
        }

        $changeSet = array(
            'username' => $this->username,
            'password' => $this->password,
        );

        if ($this->profile !== null) {
            $changeSet['profileId'] = $this->profile->getId();
        }

        if ($this->id !== null) {
            $changeSet['_id'] = (int) $this->id;
        }

        return $changeSet;
    }
}
