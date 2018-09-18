<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Hydrator;

/**
 * Interface that object hydrators must implement.
 */
interface ObjectHydratorInterface
{
    /**
     * @param object  $object
     *
     * @param mixed[] $data
     */
    public function hydrate($object, array $data) : void;
}
