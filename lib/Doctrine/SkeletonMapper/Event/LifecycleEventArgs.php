<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Lifecycle Events are triggered by the UnitOfWork during lifecycle transitions
 * of objects.
 *
 * @template-extends BaseLifecycleEventArgs<ObjectManagerInterface>
 */
class LifecycleEventArgs extends BaseLifecycleEventArgs
{
}
