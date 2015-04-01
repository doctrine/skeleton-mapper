<?php

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\BasicObjectDataRepository;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class ArrayObjectDataRepository extends BasicObjectDataRepository
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $objects;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\Common\Collections\ArrayCollection    $objects
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ArrayCollection $objects)
    {
        parent::__construct($objectManager);
        $this->objects = $objects;
    }

    public function findAll()
    {
        return $this->objects->toArray();
    }

    public function findBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null)
    {
        $objects = array();

        foreach ($this->objects as $object) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($object[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                $objects[] = $object;
            }
        }

        return $objects;
    }

    public function findOneBy(array $criteria)
    {
        foreach ($this->objects as $object) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($object[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                return $object;
            }
        }
    }
}
