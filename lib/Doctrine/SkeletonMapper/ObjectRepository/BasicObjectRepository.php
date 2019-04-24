<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use BadMethodCallException;
use function get_class;

class BasicObjectRepository extends ObjectRepository
{
    /**
     * {@inheritDoc}
     */
    public function getObjectIdentifier(object $object) : array
    {
        return $this->objectManager
            ->getClassMetadata(get_class($object))
            ->getIdentifierValues($object);
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentifierFromData(array $data) : array
    {
        $identifier = [];

        foreach ($this->class->getIdentifier() as $name) {
            $identifier[$name] = $data[$name];
        }

        return $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function merge(object $object) : object
    {
        throw new BadMethodCallException('Not implemented.');
    }
}
