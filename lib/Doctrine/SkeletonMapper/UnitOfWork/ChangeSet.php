<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

class ChangeSet
{
    /** @var object */
    private $object;

    /** @var array<string, Change> */
    private $changes = [];

    /**
     * @param Change[] $changes
     */
    public function __construct(object $object, array $changes = [])
    {
        $this->object  = $object;
        $this->changes = $changes;
    }

    public function getObject() : object
    {
        return $this->object;
    }

    public function addChange(Change $change) : void
    {
        $this->changes[$change->getPropertyName()] = $change;
    }

    /**
     * @return Change[]
     */
    public function getChanges() : array
    {
        return $this->changes;
    }

    public function hasChangedField(string $fieldName) : bool
    {
        return isset($this->changes[$fieldName]);
    }

    public function getFieldChange(string $fieldName) : ?Change
    {
        return $this->changes[$fieldName] ?? null;
    }
}
