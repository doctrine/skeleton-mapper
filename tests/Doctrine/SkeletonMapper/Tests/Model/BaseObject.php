<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
use Doctrine\SkeletonMapper\Persister\IdentifiableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

abstract class BaseObject implements HydratableInterface, PersistableInterface, IdentifiableInterface, LoadMetadataInterface, NotifyPropertyChanged
{
    /**
     * @var array
     */
    private $listeners = array();

    /**
     * @param \Doctrine\Common\PropertyChangedListener $listener
     */
    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param string $propName
     * @param mixed  $oldValue
     * @param mixed  $newValue
     */
    protected function onPropertyChanged($propName, $oldValue, $newValue)
    {
        if ($this->listeners) {
            foreach ($this->listeners as $listener) {
                $listener->propertyChanged($this, $propName, $oldValue, $newValue);
            }
        }
    }
}
