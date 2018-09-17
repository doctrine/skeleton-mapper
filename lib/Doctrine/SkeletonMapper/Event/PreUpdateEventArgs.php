<?php

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Class that holds event arguments for a preUpdate event.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class PreUpdateEventArgs extends LifecycleEventArgs
{
    /**
     * @var \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet
     */
    private $objectChangeSet;

    /**
     * Constructor.
     *
     * @param object                                          $object
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet   $changeSet
     */
    public function __construct(
        $object,
        ObjectManagerInterface $objectManager,
        ChangeSet $changeSet)
    {
        parent::__construct($object, $objectManager);
        $this->objectChangeSet = $changeSet;
    }

    /**
     * Retrieves the object changeset.
     *
     * @return \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet
     */
    public function getObjectChangeSet()
    {
        return $this->objectChangeSet;
    }

    /**
     * Checks if field has a changeset.
     *
     * @param string $field
     *
     * @return bool
     */
    public function hasChangedField($field)
    {
        return $this->objectChangeSet->hasChangedField($field);
    }

    /**
     * Gets the old value of the changeset of the changed field.
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getOldValue($field)
    {
        if ($change = $this->objectChangeSet->getFieldChange($field)) {
            return $change->getOldValue();
        }
    }

    /**
     * Gets the new value of the changeset of the changed field.
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getNewValue($field)
    {
        if ($change = $this->objectChangeSet->getFieldChange($field)) {
            return $change->getNewValue();
        }
    }

    /**
     * Sets the new value of this field.
     *
     * @param string $field
     * @param mixed  $value
     */
    public function setNewValue($field, $value)
    {
        if ($change = $this->objectChangeSet->getFieldChange($field)) {
            $change->setNewValue($value);
        } else {
            $this->objectChangeSet->addChange(new Change($field, null, $value));
        }
    }
}
