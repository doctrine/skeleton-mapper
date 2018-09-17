<?php

namespace Doctrine\SkeletonMapper\UnitOfWork;

class Change
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var mixed
     */
    private $oldValue;

    /**
     * @var mixed
     */
    private $newValue;

    /**
     * @param string $propertyName
     * @param mixed  $oldValue
     * @param mixed  $newValue
     */
    public function __construct($propertyName, $oldValue, $newValue)
    {
        $this->propertyName = $propertyName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * @return string
     */
    public function getPropertyName()
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
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
    }
}
