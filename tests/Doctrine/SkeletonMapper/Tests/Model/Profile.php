<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\IdentifiableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

class Profile implements HydratableInterface, PersistableInterface, IdentifiableInterface, LoadMetadataInterface, NotifyPropertyChanged
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
    private $name;

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
            'name' => $this->name,
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
