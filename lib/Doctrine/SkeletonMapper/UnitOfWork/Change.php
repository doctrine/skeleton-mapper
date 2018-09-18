<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

class Change
{
    /** @var string */
    private $propertyName;

    /** @var mixed */
    private $oldValue;

    /** @var mixed */
    private $newValue;

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function __construct(string $propertyName, $oldValue, $newValue)
    {
        $this->propertyName = $propertyName;
        $this->oldValue     = $oldValue;
        $this->newValue     = $newValue;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @param mixed $newValue
     */
    public function setNewValue($newValue) : void
    {
        $this->newValue = $newValue;
    }
}
