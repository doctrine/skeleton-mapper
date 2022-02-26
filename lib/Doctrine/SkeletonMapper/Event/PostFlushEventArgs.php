<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Persistence\Event\ManagerEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Provides event arguments for the postFlush event.
 *
 * @template-extends ManagerEventArgs<ObjectManagerInterface>
 */
class PostFlushEventArgs extends ManagerEventArgs
{
}
