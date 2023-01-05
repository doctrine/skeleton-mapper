<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use BadMethodCallException;

/**
 * @template T of object
 * @template-extends ObjectRepository<T>
 */
class BasicObjectRepository extends ObjectRepository
{
    /** @return mixed[] */
    public function getObjectIdentifier(object $object): array
    {
        return $this->objectManager
            ->getClassMetadata($object::class)
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

    public function merge(object $object): void
    {
        throw new BadMethodCallException('Not implemented.');
    }
}
