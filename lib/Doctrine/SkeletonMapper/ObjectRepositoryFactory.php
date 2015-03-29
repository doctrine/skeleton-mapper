<?php

namespace Doctrine\SkeletonMapper;

class ObjectRepositoryFactory implements ObjectRepositoryFactoryInterface
{
    /**
     * @var array
     */
    private $repositories = array();

    /**
     * @param string                                        $className
     * @param \Doctrine\Common\Persistence\ObjectRepository $objectRepository
     */
    public function addObjectRepository($className, $objectRepository)
    {
        $this->repositories[$className] = $objectRepository;
    }

    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        if (!isset($this->repositories[$className])) {
            throw new \InvalidArgumentException(sprintf('ObjectRepository with class name %s was not found', $className));
        }

        return $this->repositories[$className];
    }
}
