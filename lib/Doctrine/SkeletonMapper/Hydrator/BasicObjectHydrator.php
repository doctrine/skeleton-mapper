<?php

namespace Doctrine\SkeletonMapper\Hydrator;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Basic object hydrator that delegates hydration
 * to a method on the object that is being hydrated
 * or uses a dynamic hydration algorithm.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class BasicObjectHydrator extends ObjectHydrator
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager $eventManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Doctrine\SkeletonMapper\Hydrator\HydratableInterface $object
     * @param array                                                 $data
     */
    public function hydrate($object, array $data)
    {
        if (!$object instanceof HydratableInterface) {
            throw new \InvalidArgumentException(sprintf('%s must implement HydratableInterface.', get_class($object)));
        }

        $object->hydrate($data, $this->objectManager);
    }
}
