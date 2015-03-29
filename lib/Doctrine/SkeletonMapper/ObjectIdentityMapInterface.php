<?php

namespace Doctrine\SkeletonMapper;

interface ObjectIdentityMapInterface
{
    /**
     * @param object $object
     *
     * @return bool
     */
    public function contains($object);

    /**
     * @param string $className
     * @param array  $data
     *
     * @return object
     */
    public function tryGetById($className, array $data);

    /**
     * @param object $object
     * @param array  $data
     */
    public function addToIdentityMap($object, array $data);

    /**
     * @param string|null $objectName
     */
    public function clear($objectName = null);

    /**
     * @param object $object
     */
    public function detach($object);
}
