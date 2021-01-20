<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

/**
 * Class responsible for retrieving ObjectRepository instances.
 */
interface ObjectRepositoryFactoryInterface
{
    public function getRepository(string $className): ObjectRepositoryInterface;
}
