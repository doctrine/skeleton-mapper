<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Class that holds event arguments for a preUpdate event.
 */
class PreUpdateEventArgs extends LifecycleEventArgs
{
    /** @var ChangeSet */
    private $objectChangeSet;

    /**
     * @param object $object
     */
    public function __construct(
        $object,
        ObjectManagerInterface $objectManager,
        ChangeSet $changeSet
    ) {
        parent::__construct($object, $objectManager);
        $this->objectChangeSet = $changeSet;
    }

    /**
     * Retrieves the object changeset.
     */
    public function getObjectChangeSet(): ChangeSet
    {
        return $this->objectChangeSet;
    }

    /**
     * Checks if field has a changeset.
     */
    public function hasChangedField(string $field): bool
    {
        return $this->objectChangeSet->hasChangedField($field);
    }

    /**
     * Gets the old value of the changeset of the changed field.
     *
     * @return mixed
     */
    public function getOldValue(string $field)
    {
        $change = $this->objectChangeSet->getFieldChange($field);

        if ($change !== null) {
            return $change->getOldValue();
        }
    }

    /**
     * Gets the new value of the changeset of the changed field.
     *
     * @return mixed
     */
    public function getNewValue(string $field)
    {
        $change = $this->objectChangeSet->getFieldChange($field);

        if ($change !== null) {
            return $change->getNewValue();
        }
    }

    /**
     * Sets the new value of this field.
     *
     * @param mixed $value
     */
    public function setNewValue(string $field, $value): void
    {
        $change = $this->objectChangeSet->getFieldChange($field);

        if ($change !== null) {
            $change->setNewValue($value);
        } else {
            $this->objectChangeSet->addChange(new Change($field, null, $value));
        }
    }
}
