<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

/**
 * Base class for object persisters to extend from.
 *
 * @template T of object
 * @template-implements ObjectPersisterInterface<T>
 */
abstract class ObjectPersister implements ObjectPersisterInterface
{
}
