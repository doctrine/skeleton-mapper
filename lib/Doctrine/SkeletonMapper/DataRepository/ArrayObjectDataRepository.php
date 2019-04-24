<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class ArrayObjectDataRepository extends BasicObjectDataRepository
{
    /** @var ArrayCollection */
    private $objects;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ArrayCollection $objects,
        string $className
    ) {
        parent::__construct($objectManager, $className);
        $this->objects = $objects;
    }

    /**
     * @return array<int, mixed[]>
     */
    public function findAll() : array
    {
        return $this->objects->toArray();
    }

    /**
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $orderBy
     *
     * @return array<int, array<string, mixed>>
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) : array {
        $objects = [];

        foreach ($this->objects as $object) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($object[$key] === $value) {
                    continue;
                }

                $matches = false;
            }

            if (! $matches) {
                continue;
            }

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>|null
     */
    public function findOneBy(array $criteria) : ?array
    {
        foreach ($this->objects as $object) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($object[$key] === $value) {
                    continue;
                }

                $matches = false;
            }

            if ($matches) {
                return $object;
            }
        }

        return null;
    }
}
