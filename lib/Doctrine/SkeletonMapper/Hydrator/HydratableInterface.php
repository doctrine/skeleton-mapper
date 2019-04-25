<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Hydrator;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Interface hydratable objects must implement.
 */
interface HydratableInterface
{
    /**
     * @param  array<string, mixed> $data
     */
    public function hydrate(array $data, ObjectManagerInterface $objectManager) : void;
}
