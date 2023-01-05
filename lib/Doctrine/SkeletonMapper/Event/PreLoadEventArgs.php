<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Class that holds event arguments for a preLoad event.
 */
class PreLoadEventArgs extends LifecycleEventArgs
{
    /** @var mixed[] */
    private array $data;

    /** @param mixed[] $data Array of data to be loaded and hydrated */
    public function __construct(object $object, ObjectManagerInterface $objectManager, array &$data)
    {
        parent::__construct($object, $objectManager);

        $this->data = &$data;
    }

    /**
     * Get the array of data to be loaded and hydrated.
     *
     * @return mixed[]
     */
    public function &getData(): array
    {
        return $this->data;
    }
}
