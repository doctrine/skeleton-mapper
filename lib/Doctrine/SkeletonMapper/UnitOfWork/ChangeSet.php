<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

class ChangeSet
{
    /** @var object */
    private $object;

    /** @var Change[] */
    private $changes = [];

    /**
     * @param object   $object
     * @param Change[] $changes
     */
    public function __construct($object, array $changes = [])
    {
        $this->object  = $object;
        $this->changes = $changes;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    public function addChange(Change $change): void
    {
        $this->changes[$change->getPropertyName()] = $change;
    }

    /**
     * @return Change[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    public function hasChangedField(string $fieldName): bool
    {
        return isset($this->changes[$fieldName]);
    }

    public function getFieldChange(string $fieldName): ?Change
    {
        return $this->changes[$fieldName] ?? null;
    }
}
