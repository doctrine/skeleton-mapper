<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Persister;

/**
 * Class responsible for retrieving ObjectPersister instances.
 */
interface ObjectPersisterFactoryInterface
{
    public function getPersister(string $className) : ObjectPersisterInterface;

    /**
     * @return array<string, ObjectPersisterInterface>
     */
    public function getPersisters() : array;
}
