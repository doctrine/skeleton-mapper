<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

class ChangeSet
{
    /** @param Change[] $changes */
    public function __construct(private object $object, private array $changes = [])
    {
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function addChange(Change $change): void
    {
        $this->changes[$change->getPropertyName()] = $change;
    }

    /** @return Change[] */
    public function getChanges(): array
    {
        return $this->changes;
    }

    public function hasChangedField(string $fieldName): bool
    {
        return isset($this->changes[$fieldName]);
    }

    public function getFieldChange(string $fieldName): Change|null
    {
        return $this->changes[$fieldName] ?? null;
    }
}
