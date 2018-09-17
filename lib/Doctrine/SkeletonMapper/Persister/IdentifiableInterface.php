<?php

namespace Doctrine\SkeletonMapper\Persister;

interface IdentifiableInterface
{
    /**
     * Assign identifier to object.
     *
     * @param array $identifier
     */
    public function assignIdentifier(array $identifier);
}
