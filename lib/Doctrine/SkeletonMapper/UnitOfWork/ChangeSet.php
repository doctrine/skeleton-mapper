<?php

namespace Doctrine\SkeletonMapper\UnitOfWork;

class ChangeSet
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $changes = array();

    /**
     * @param object $object
     * @param array  $changes
     */
    public function __construct($object, array $changes = array())
    {
        $this->object = $object;
        $this->changes = $changes;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param \Doctrine\SkeletonMapper\UnitOfWork\Change $change
     */
    public function addChange(Change $change)
    {
        $this->changes[$change->getPropertyName()] = $change;
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasChangedField($fieldName)
    {
        return isset($this->changes[$fieldName]);
    }

    /**
     * @param string $fieldName
     *
     * @return \Doctrine\SkeletonMapper\UnitOfWork\Change
     */
    public function getFieldChange($fieldName)
    {
        return isset($this->changes[$fieldName]) ? $this->changes[$fieldName] : null;
    }
}
