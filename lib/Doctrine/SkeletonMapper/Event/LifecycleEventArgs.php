<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;

/**
 * Lifecycle Events are triggered by the UnitOfWork during lifecycle transitions
 * of objects.
 */
class LifecycleEventArgs extends BaseLifecycleEventArgs
{
}
