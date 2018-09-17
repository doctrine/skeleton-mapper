<?php

namespace Doctrine\SkeletonMapper\Hydrator;

/**
 * Interface that object hydrators must implement.
 */
interface ObjectHydratorInterface
{
    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data);
}
