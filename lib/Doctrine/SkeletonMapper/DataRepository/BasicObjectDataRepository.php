<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use RuntimeException;
use ValueError;

use function array_combine;
use function is_array;

abstract class BasicObjectDataRepository extends ObjectDataRepository
{
    /** @param class-string $className */
    public function __construct(protected ObjectManagerInterface $objectManager, protected string $className)
    {
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }

    /** @param class-string $className */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /** @return mixed[] */
    public function find(mixed $id): array|null
    {
        $identifier = $this->getIdentifier();

        $identifierValues = is_array($id) ? $id : [$id];

        try {
            $criteria = array_combine($identifier, $identifierValues);
        } catch (ValueError) {
            throw new RuntimeException('array_combine failed. Make sure you passed a value for each identifier.');
        }

        return $this->findOneBy($criteria);
    }

    /** @return mixed[] */
    protected function getIdentifier(): array
    {
        return $this->objectManager
            ->getClassMetadata($this->getClassName())
            ->getIdentifier();
    }

    /** @return mixed[] */
    protected function getObjectIdentifier(object $object): array
    {
        return $this->objectManager
            ->getRepository($this->getClassName())
            ->getObjectIdentifier($object);
    }
}
