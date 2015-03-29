<?php

namespace Doctrine\SkeletonMapper;

interface ObjectHydratorInterface
{
    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data);
}
