<?php

namespace Doctrine\ORMLess;

interface ObjectDataRepositoryInterface
{
    /**
     * Finds an objects data by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return array The objects array of data.
     */
    public function find($id);

    /**
     * Finds an objects data by the object.
     *
     * @param object $object
     *
     * @return array The objects array of data.
     */
    public function findByObject($object);

    /**
     * Finds all object data in the repository.
     *
     * @return array The objects data.
     */
    public function findAll();

    /**
     * Finds objects data by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Finds a single objects data by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return array The objects array of data
     */
    public function findOneBy(array $criteria);
}
