<?php

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Class that holds event arguments for a preLoad event.
 */
class PreLoadEventArgs extends LifecycleEventArgs
{
    /**
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param object                 $object
     * @param ObjectManagerInterface $objectManager
     * @param array                  $data          Array of data to be loaded and hydrated
     */
    public function __construct($object, ObjectManagerInterface $objectManager, array &$data)
    {
        parent::__construct($object, $objectManager);
        $this->data = & $data;
    }

    /**
     * Get the array of data to be loaded and hydrated.
     *
     * @return array
     */
    public function &getData()
    {
        return $this->data;
    }
}
