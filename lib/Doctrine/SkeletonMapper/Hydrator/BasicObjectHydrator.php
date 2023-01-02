<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Hydrator;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use InvalidArgumentException;

use function sprintf;

/**
 * Basic object hydrator that delegates hydration
 * to a method on the object that is being hydrated
 * or uses a dynamic hydration algorithm.
 */
class BasicObjectHydrator extends ObjectHydrator
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param object  $object
     * @param mixed[] $data
     */
    public function hydrate($object, array $data): void
    {
        if (! $object instanceof HydratableInterface) {
            throw new InvalidArgumentException(sprintf(
                'Class %s does not implement %s.',
                $object::class,
                HydratableInterface::class,
            ));
        }

        $object->hydrate($data, $this->objectManager);
    }
}
