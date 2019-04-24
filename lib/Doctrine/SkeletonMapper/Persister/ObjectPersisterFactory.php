<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

use InvalidArgumentException;
use function sprintf;

/**
 * Class responsible for retrieving ObjectPersister instances.
 */
class ObjectPersisterFactory implements ObjectPersisterFactoryInterface
{
    /** @var array<string, ObjectPersisterInterface> */
    private $persisters = [];

    public function addObjectPersister(string $className, ObjectPersisterInterface $objectPersister) : void
    {
        $this->persisters[$className] = $objectPersister;
    }

    public function getPersister(string $className) : ObjectPersisterInterface
    {
        if (! isset($this->persisters[$className])) {
            throw new InvalidArgumentException(sprintf('ObjectPersister with class name %s was not found', $className));
        }

        return $this->persisters[$className];
    }

    /**
     * @return array<string, ObjectPersisterInterface>
     */
    public function getPersisters() : array
    {
        return $this->persisters;
    }
}
