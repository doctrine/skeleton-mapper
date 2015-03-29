<?php

namespace Doctrine\ORMLess;

abstract class ObjectRepository implements ObjectRepositoryInterface
{
    /**
     * @var \Doctrine\ORMLess\ObjectDataRepositoryInterface
     */
    private $objectDataRepository;

    /**
     * @var \Doctrine\ORMLess\ObjectFactoryInterface
     */
    protected $objectFactory;

    /**
     * @var \Doctrine\ORMLess\ObjectHydratorInterface
     */
    protected $objectHydrator;

    /**
     * @var \Doctrine\ORMLess\ObjectIdentityMapInterface
     */
    protected $objectIdentityMap;

    /**
     * @param \Doctrine\ORMLess\ObjectDataRepositoryInterface $objectDataRepository
     * @param \Doctrine\ORMLess\ObjectFactoryInterface        $objectFactory
     * @param \Doctrine\ORMLess\ObjectHydratorInterface       $objectHydrator
     * @param \Doctrine\ORMLess\ObjectIdentityMapInterface    $objectIdentityMap
     */
    public function __construct(
        ObjectDataRepositoryInterface $objectDataRepository,
        ObjectFactoryInterface $objectFactory,
        ObjectHydratorInterface $objectHydrator,
        ObjectIdentityMapInterface $objectIdentityMap)
    {
        $this->objectDataRepository = $objectDataRepository;
        $this->objectFactory = $objectFactory;
        $this->objectHydrator = $objectHydrator;
        $this->objectIdentityMap = $objectIdentityMap;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        $data = $this->objectDataRepository->find($id);

        if ($data === null) {
            return;
        }

        return $this->getOrCreateObject($data);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        $objectsData = $this->objectDataRepository->findAll();

        $objects = array();
        foreach ($objectsData as $objectData) {
            $objects[] = $this->getOrCreateObject($objectData);
        }

        return $objects;
    }

    /**
     * Finds objects by a set of criteria.
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
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $objectsData = $this->objectDataRepository->findBy(
            $criteria, $orderBy, $limit, $offset
        );

        $objects = array();
        foreach ($objectsData as $objectData) {
            $objects[] = $this->getOrCreateObject($objectData);
        }

        return $objects;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $data = $this->objectDataRepository->findOneBy($criteria);

        if ($data === null) {
            return;
        }

        return $this->getOrCreateObject($data);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        $data = $this->objectDataRepository->findByObject($object);

        $this->objectHydrator->hydrate($object, $data);
    }

    /**
     * @param mixed $id
     * @param array $data
     *
     * @return object
     */
    private function getOrCreateObject(array $data)
    {
        $className = $this->getClassName();
        $object = $this->objectIdentityMap->tryGetById($className, $data);

        if (!$object) {
            $object = $this->objectFactory->create($className);
            $this->objectHydrator->hydrate($object, $data);

            $this->objectIdentityMap->addToIdentityMap($object, $data);
        }

        return $object;
    }
}
