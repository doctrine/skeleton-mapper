<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use BadMethodCallException;

use function get_class;

class BasicObjectRepository extends ObjectRepository
{
    /**
     * @param object $object
     *
     * @return mixed[]
     */
    public function getObjectIdentifier($object): array
    {
        return $this->objectManager
            ->getClassMetadata(get_class($object))
            ->getIdentifierValues($object);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function getObjectIdentifierFromData(array $data): array
    {
        $identifier = [];

        foreach ($this->class->getIdentifier() as $name) {
            $identifier[$name] = $data[$name];
        }

        return $identifier;
    }

    /**
     * @param object $object
     */
    public function merge($object): void
    {
        throw new BadMethodCallException('Not implemented.');
    }
}
