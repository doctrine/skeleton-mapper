<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use InvalidArgumentException;
use function sprintf;

/**
 * Class responsible for retrieving ObjectRepository instances.
 */
class ObjectRepositoryFactory implements ObjectRepositoryFactoryInterface
{
    /** @var ObjectRepositoryInterface[] */
    private $repositories = [];

    public function addObjectRepository(
        string $className,
        ObjectRepositoryInterface $objectRepository
    ) : void {
        $this->repositories[$className] = $objectRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $className) : ObjectRepositoryInterface
    {
        if (! isset($this->repositories[$className])) {
            throw new InvalidArgumentException(
                sprintf('ObjectRepository with class name %s was not found', $className)
            );
        }

        return $this->repositories[$className];
    }
}
