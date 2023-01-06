<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\SkeletonMapper\Tests\Model\Identifiable;
use Doctrine\SkeletonMapper\Tests\Model\User;

use function assert;

class ObjectRepository extends BasicObjectRepository
{
    /** @return mixed[] */
    public function getObjectIdentifier(object $object): array
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

    public function merge(object $object): void
    {
        assert($object instanceof User);

        $user = $this->find($object->getId());
        assert($user instanceof User);

        $user->setUsername($object->getUsername());
        $user->setPassword($object->getPassword());
    }
}
