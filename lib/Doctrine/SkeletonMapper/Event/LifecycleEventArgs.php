<?php

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;

/**
 * Lifecycle Events are triggered by the UnitOfWork during lifecycle transitions
 * of objects.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class LifecycleEventArgs extends BaseLifecycleEventArgs
{
}
