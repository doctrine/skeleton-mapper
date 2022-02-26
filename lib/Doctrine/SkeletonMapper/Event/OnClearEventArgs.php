<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Persistence\Event\OnClearEventArgs as BaseOnClearEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Provides event arguments for the onClear event.
 *
 * @template-extends BaseOnClearEventArgs<ObjectManagerInterface>
 */
class OnClearEventArgs extends BaseOnClearEventArgs
{
    /**
     * Returns the name of the object class that is cleared, or null if all
     * are cleared.
     */
    public function getObjectClass(): ?string
    {
        return $this->getEntityClass();
    }

    /**
     * Returns whether this event clears all objects.
     */
    public function clearsAllObjects(): bool
    {
        return $this->clearsAllEntities();
    }
}
