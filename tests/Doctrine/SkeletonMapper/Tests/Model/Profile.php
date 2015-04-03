<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

class Profile extends BaseObject
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\SkeletonMapper\Tests\Model\Address
     */
    private $address;

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
            'fieldName' => 'name',
        ));
        $metadata->mapField(array(
            'fieldName' => 'address',
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $name = (string) $name;

        if ($this->name !== $name) {
            $this->onPropertyChanged('name', $this->name, $name);
            $this->name = $name;
        }
    }

    public function getAddress()
    {
        if ($this->address instanceof \Closure) {
            $this->address = $this->address->__invoke();
        }

        return $this->address;
    }

    public function setAddress(Address $address)
    {
        if ($this->address != $address) {
            $this->onPropertyChanged('address', $this->address, $address);
            $this->address = $address;
        }
    }

    public function addressChanged($propName, $oldValue, $newValue)
    {
        $this->onPropertyChanged('address', $this->address, $this->address);
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

        if (isset($data['name'])) {
            $this->name = (string) $data['name'];
        }

        if (isset($data['address1'])
            || isset($data['address2'])
            || isset($data['city'])
            || isset($data['state'])
            || isset($data['zip'])) {
            $profile = $this;
            $this->address = function () use ($data, $profile) {
                $address = new Address($profile);

                if (isset($data['address1'])) {
                    $address->setAddress1($data['address1']);
                }

                if (isset($data['address2'])) {
                    $address->setAddress2($data['address2']);
                }

                if (isset($data['city'])) {
                    $address->setCity($data['city']);
                }

                if (isset($data['state'])) {
                    $address->setState($data['state']);
                }

                if (isset($data['zip'])) {
                    $address->setZip($data['zip']);
                }

                return $address;
            };
        }
    }

    /**
     * @see PersistableInterface
     *
     * @param array $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet(array $changeSet)
    {
        $changeSet = array_map(function ($change) {
            return $change[1];
        }, $changeSet);

        $changeSet['_id'] = (int) $this->id;

        if ($address = $changeSet['address']) {
            unset($changeSet['address']);

            $changeSet['address1'] = $address->getAddress1();
            $changeSet['address2'] = $address->getAddress2();
            $changeSet['city'] = $address->getCity();
            $changeSet['state'] = $address->getState();
            $changeSet['zip'] = $address->getZip();
        }

        return $changeSet;
    }

    /**
     * @see PersistableInterface
     *
     * @return array
     */
    public function preparePersistChangeSet()
    {
        $changeSet = array(
            'name' => $this->name,
        );

        if ($this->address !== null) {
            $changeSet['address1'] = $this->address->getAddress1();
            $changeSet['address2'] = $this->address->getAddress2();
            $changeSet['city'] = $this->address->getCity();
            $changeSet['state'] = $this->address->getState();
            $changeSet['zip'] = $this->address->getZip();
        }

        if ($this->id !== null) {
            $changeSet['_id'] = (int) $this->id;
        }

        return $changeSet;
    }
}
