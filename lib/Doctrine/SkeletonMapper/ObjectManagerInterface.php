<?php

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\Persistence\ObjectManager as BaseObjectManagerInterface;

/**
 * Interface that object managers must implement.
 */
interface ObjectManagerInterface extends BaseObjectManagerInterface
{
    /**
     * @param object $object
     */
    public function update($object);

    /**
     * @param string $className
     * @param array $data
     *
     * @return object
     */
    public function getOrCreateObject($className, array $data);

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork();
}
