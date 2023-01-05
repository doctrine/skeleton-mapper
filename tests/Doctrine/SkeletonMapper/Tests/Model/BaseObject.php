<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Persistence\NotifyPropertyChanged;
use Doctrine\Persistence\PropertyChangedListener;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
use Doctrine\SkeletonMapper\Persister\IdentifiableInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;

abstract class BaseObject implements HydratableInterface, PersistableInterface, IdentifiableInterface, LoadMetadataInterface, NotifyPropertyChanged, Identifiable
{
    /** @var PropertyChangedListener[] */
    private array $listeners = [];

    public function addPropertyChangedListener(PropertyChangedListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    protected function onPropertyChanged(string $propName, mixed $oldValue, mixed $newValue): void
    {
        if ($this->listeners === []) {
            return;
        }

        foreach ($this->listeners as $listener) {
            $listener->propertyChanged($this, $propName, $oldValue, $newValue);
        }
    }
}
