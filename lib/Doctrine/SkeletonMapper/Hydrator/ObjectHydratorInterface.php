<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Hydrator;

/**
 * Interface that object hydrators must implement.
 */
interface ObjectHydratorInterface
{
    /** @param mixed[] $data */
    public function hydrate(object $object, array $data): void;
}
