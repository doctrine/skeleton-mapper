<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

/**
 * Class responsible for retrieving ObjectRepository instances.
 */
interface ObjectRepositoryFactoryInterface
{
    /**
     * @param class-string<T> $className
     *
     * @return ObjectRepositoryInterface<T>
     *
     * @template T of object
     */
    public function getRepository(string $className): ObjectRepositoryInterface;
}
