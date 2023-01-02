<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

class Change
{
    /** @var string */
    private $propertyName;

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function __construct(string $propertyName, private $oldValue, private $newValue)
    {
        $this->propertyName = $propertyName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /** @return mixed */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /** @return mixed */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /** @param mixed $newValue */
    public function setNewValue($newValue): void
    {
        $this->newValue = $newValue;
    }
}
