<?php

namespace Doctrine\ORMLess;

interface ObjectHydratorInterface
{
    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data);
}
