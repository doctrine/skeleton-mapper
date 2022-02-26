<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use InvalidArgumentException;

use function sprintf;

/**
 * Class responsible for retrieving ObjectRepository instances.
 *
 * @template T of object
 */
class ObjectRepositoryFactory implements ObjectRepositoryFactoryInterface
{
    /** @var array<ObjectRepositoryInterface<T>> */
    private $repositories = [];

    /**
     * @phpstan-param class-string<T>                 $className
     * @phpstan-param ObjectRepositoryInterface<T>    $objectRepository
     */
    public function addObjectRepository(string $className, ObjectRepositoryInterface $objectRepository): void
    {
        $this->repositories[$className] = $objectRepository;
    }

    /**
     * @phpstan-param class-string<T> $className
     *
     * @phpstan-return ObjectRepositoryInterface<T>
     */
    public function getRepository(string $className): ObjectRepositoryInterface
    {
        if (! isset($this->repositories[$className])) {
            throw new InvalidArgumentException(
                sprintf('ObjectRepository with class name %s was not found', $className)
            );
        }

        return $this->repositories[$className];
    }
}
