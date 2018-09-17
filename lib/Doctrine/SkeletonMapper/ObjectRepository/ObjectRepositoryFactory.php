<?php

namespace Doctrine\SkeletonMapper\ObjectRepository;

/**
 * Class responsible for retrieving ObjectRepository instances.
 */
class ObjectRepositoryFactory implements ObjectRepositoryFactoryInterface
{
    /**
     * @var array
     */
    private $repositories = array();

    /**
     * @param string                                                              $className
     * @param \Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface $objectRepository
     */
    public function addObjectRepository($className, ObjectRepositoryInterface $objectRepository)
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
