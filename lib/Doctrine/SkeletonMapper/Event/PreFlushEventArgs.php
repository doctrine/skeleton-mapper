<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Persistence\Event\ManagerEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Provides event arguments for the preFlush event.
 *
 * @template-extends ManagerEventArgs<ObjectManagerInterface>
 */
class PreFlushEventArgs extends ManagerEventArgs
{
}
