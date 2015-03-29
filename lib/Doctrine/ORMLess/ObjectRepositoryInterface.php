<?php

namespace Doctrine\ORMLess;

use Doctrine\Common\Persistence\ObjectRepository as BaseObjectRepositoryInterface;

interface ObjectRepositoryInterface extends BaseObjectRepositoryInterface
{
    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Returns the objects identifier.
     *
     * @return array
     */
    public function getObjectIdentifier($object);

    /**
     * Returns the classes identifier field names.
     *
     * @return array
     */
    public function getIdentifierFieldNames();

    /**
     * @param object $object
     */
    public function merge($object);
}
