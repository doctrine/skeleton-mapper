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
}
