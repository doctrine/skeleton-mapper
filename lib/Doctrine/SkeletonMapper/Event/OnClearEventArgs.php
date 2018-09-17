<?php

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Common\Persistence\Event\OnClearEventArgs as BaseOnClearEventArgs;

/**
 * Provides event arguments for the onClear event.
 */
class OnClearEventArgs extends BaseOnClearEventArgs
{
    /**
     * Returns the name of the object class that is cleared, or null if all
     * are cleared.
     *
     * @return string|null
     */
    public function getObjectClass()
    {
        return $this->getEntityClass();
    }

    /**
     * Returns whether this event clears all objects.
     *
     * @return bool
     */
    public function clearsAllObjects()
    {
        return $this->clearsAllEntities();
    }
}
