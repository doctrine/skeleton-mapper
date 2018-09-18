<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\Common\Persistence\Event\ManagerEventArgs;

/**
 * Provides event arguments for the postFlush event.
 */
class PostFlushEventArgs extends ManagerEventArgs
{
}
