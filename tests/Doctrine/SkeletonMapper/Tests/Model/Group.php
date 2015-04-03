<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

class Group extends BaseObject
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

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

        if ($this->id !== null) {
            $changeSet['_id'] = (int) $this->id;
        }

        return $changeSet;
    }

    /**
     * @see PersistableInterface
     *
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet(ChangeSet $changeSet)
    {
        $changeSet = array_map(function ($change) {
            return $change[1];
        }, $changeSet);

        $changeSet['_id'] = (int) $this->id;

        return $changeSet;
    }
}
