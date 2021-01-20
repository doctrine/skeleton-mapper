<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
use Doctrine\SkeletonMapper\Persister\IdentifiableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

abstract class BaseObject implements HydratableInterface, PersistableInterface, IdentifiableInterface, LoadMetadataInterface, NotifyPropertyChanged, Identifiable
{
    /** @var PropertyChangedListener[] */
    private $listeners = [];

    public function addPropertyChangedListener(PropertyChangedListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    protected function onPropertyChanged(string $propName, $oldValue, $newValue): void
    {
        if ($this->listeners === []) {
            return;
        }

        foreach ($this->listeners as $listener) {
            $listener->propertyChanged($this, $propName, $oldValue, $newValue);
        }
    }
}
