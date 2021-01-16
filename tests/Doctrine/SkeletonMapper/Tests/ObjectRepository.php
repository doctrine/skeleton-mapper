<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\SkeletonMapper\Tests\Model\Identifiable;
use Doctrine\SkeletonMapper\Tests\Model\User;

use function assert;

class ObjectRepository extends BasicObjectRepository
{
    /**
     * @param object $object
     *
     * @return mixed[]
     */
    public function getObjectIdentifier($object): array
    {
        assert($object instanceof Identifiable);

        return ['_id' => $object->getId()];
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function getObjectIdentifierFromData(array $data): array
    {
        return ['_id' => $data['_id']];
    }

    /**
     * @param object $object
     */
    public function merge($object): void
    {
        assert($object instanceof User);

        $user = $this->find($object->getId());
        assert($user instanceof User);

        $user->setUsername($object->getUsername());
        $user->setPassword($object->getPassword());
    }
}
