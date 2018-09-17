<?php

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Common\Persistence\ObjectRepository as BaseObjectRepositoryInterface;

/**
 * Interface that object repositories must implement.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
interface ObjectRepositoryInterface extends BaseObjectRepositoryInterface
{
    /**
     * Returns the objects identifier.
     *
     * @return array
     */
    public function getObjectIdentifier($object);

    /**
     * Returns the identifier.
     *
     * @return array
     */
    public function getObjectIdentifierFromData(array $data);

    /**
     * @param object $object
     */
    public function merge($object);

    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data);

    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className);
}
