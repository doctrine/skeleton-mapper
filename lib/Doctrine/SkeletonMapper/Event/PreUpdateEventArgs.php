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
    private ChangeSet $objectChangeSet;

    public function __construct(
        object $object,
        ObjectManagerInterface $objectManager,
        ChangeSet $changeSet,
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
     */
    public function getOldValue(string $field): mixed
    {
        $change = $this->objectChangeSet->getFieldChange($field);

        if ($change !== null) {
            return $change->getOldValue();
        }

        return null;
    }

    /**
     * Gets the new value of the changeset of the changed field.
     */
    public function getNewValue(string $field): mixed
    {
        $change = $this->objectChangeSet->getFieldChange($field);

        if ($change !== null) {
            return $change->getNewValue();
        }

        return null;
    }

    /**
     * Sets the new value of this field.
     */
    public function setNewValue(string $field, mixed $value): void
    {
        $change = $this->objectChangeSet->getFieldChange($field);

        if ($change !== null) {
            $change->setNewValue($value);
        } else {
            $this->objectChangeSet->addChange(new Change($field, null, $value));
        }
    }
}
