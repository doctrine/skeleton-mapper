<?php

namespace Doctrine\ORMLess;

class UnitOfWork implements UnitOfWorkInterface
{
    /**
     * @var \Doctrine\ORMLess\ObjectPersisterFactory
     */
    protected $objectPersisterFactory;

    /**
     * @var \Doctrine\ORMLess\ObjectRepositoryFactory
     */
    protected $objectRepositoryFactory;

    /**
     * @var \Doctrine\ORMLess\ObjectIdentityMap
     */
    protected $objectIdentityMap;

    /**
     * @param \Doctrine\ORMLess\ObjectPersisterFactory  $objectPersisterFactory
     * @param \Doctrine\ORMLess\ObjectRepositoryFactory $objectRepositoryFactory
     * @param \Doctrine\ORMLess\ObjectIdentityMap       $objectIdentityMap
     */
    public function __construct(
        ObjectPersisterFactory $objectPersisterFactory,
        ObjectRepositoryFactory $objectRepositoryFactory,
        ObjectIdentityMap $objectIdentityMap)
    {
        $this->objectPersisterFactory = $objectPersisterFactory;
        $this->objectRepositoryFactory = $objectRepositoryFactory;
        $this->objectIdentityMap = $objectIdentityMap;
    }

    /**
     * @param object $object
     */
    public function merge($object)
    {
        $this->objectRepositoryFactory
            ->getRepository(get_class($object))
            ->merge($object);
    }

    /**
     * @param string|null $objectName
     */
    public function clear($objectName = null)
    {
        $this->objectIdentityMap->clear($objectName);

        $persisters = $this->objectPersisterFactory->getPersisters();

        $persistersToClear = array_filter($persisters, function (ObjectPersisterInterface $persister) use ($objectName) {
            return $objectName === null || $persister->getClassName() === $objectName;
        });

        foreach ($persistersToClear as $persister) {
            $persister->clear();
        }
    }

    /**
     * @param object $object
     */
    public function detach($object)
    {
        $this->objectIdentityMap->detach($object);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        $this->objectRepositoryFactory
            ->getRepository(get_class($object))
            ->refresh($object);
    }

    /**
     * @param object $object
     */
    public function contains($object)
    {
        return $this->objectIdentityMap->contains($object)
            || $this->objectPersisterFactory->getPersister(get_class($object))->isScheduledForPersist($object);
    }

    /**
     */
    public function commit()
    {
        $persisters = $this->objectPersisterFactory->getPersisters();

        foreach ($persisters as $persister) {
            $persister->commit();
        }
    }
}
