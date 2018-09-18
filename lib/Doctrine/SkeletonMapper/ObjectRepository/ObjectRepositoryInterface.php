<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Common\Persistence\ObjectRepository as BaseObjectRepositoryInterface;

/**
 * Interface that object repositories must implement.
 */
interface ObjectRepositoryInterface extends BaseObjectRepositoryInterface
{
    /**
     * Returns the objects identifier.
     *
     * @param object $object
     *
     * @return mixed[]
     */
    public function getObjectIdentifier($object) : array;

    /**
     * Returns the identifier.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function getObjectIdentifierFromData(array $data) : array;

    /**
     * @param object $object
     */
    public function merge($object) : void;

    /**
     * @param object  $object
     * @param mixed[] $data
     */
    public function hydrate($object, array $data) : void;

    /**
     * @return object
     */
    public function create(string $className);

    /**
     * @param object $object
     */
    public function refresh($object) : void;
}
