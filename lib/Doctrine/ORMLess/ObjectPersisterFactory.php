<?php

namespace Doctrine\ORMLess;

class ObjectPersisterFactory implements ObjectPersisterFactoryInterface
{
    /**
     * @var array
     */
    private $persisters = array();

    /**
     * @param string                                                $className
     * @param \Doctrine\Common\Persistence\ObjectPersisterInterface $objectPersister
     */
    public function addObjectPersister($className, $objectPersister)
    {
        $this->persisters[$className] = $objectPersister;
    }

    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectPersisterInterface
     */
    public function getPersister($className)
    {
        if (!isset($this->persisters[$className])) {
            throw new \InvalidArgumentException(sprintf('ObjectPersister with class name %s was not found', $className));
        }

        return $this->persisters[$className];
    }

    /**
     * @return array
     */
    public function getPersisters()
    {
        return $this->persisters;
    }
}
