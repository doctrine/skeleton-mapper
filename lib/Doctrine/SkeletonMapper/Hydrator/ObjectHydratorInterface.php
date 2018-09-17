<?php

namespace Doctrine\SkeletonMapper\Hydrator;

/**
 * Interface that object hydrators must implement.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
interface ObjectHydratorInterface
{
    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data);
}
