<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

class Change
{
    public function __construct(private string $propertyName, private mixed $oldValue, private mixed $newValue)
    {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }

    public function getNewValue(): mixed
    {
        return $this->newValue;
    }

    public function setNewValue(mixed $newValue): void
    {
        $this->newValue = $newValue;
    }
}
