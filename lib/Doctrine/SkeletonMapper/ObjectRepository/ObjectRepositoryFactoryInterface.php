<?php

namespace Doctrine\SkeletonMapper\ObjectRepository;

/**
 * Class responsible for retrieving ObjectRepository instances.
 *
 * @author Magnus Nordlander <magnus@fervo.se>
 */
interface ObjectRepositoryFactoryInterface
{
    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className);
}
