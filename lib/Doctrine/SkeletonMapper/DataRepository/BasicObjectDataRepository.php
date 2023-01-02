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
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var class-string */
    protected $className;

    /** @param class-string $className */
    public function __construct(ObjectManagerInterface $objectManager, string $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
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

    /**
     * @param mixed $id
     *
     * @return mixed[]
     */
    public function find($id): array|null
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

    /**
     * @param object $object
     *
     * @return mixed[]
     */
    protected function getObjectIdentifier($object): array
    {
        return $this->objectManager
            ->getRepository($this->getClassName())
            ->getObjectIdentifier($object);
    }
}
