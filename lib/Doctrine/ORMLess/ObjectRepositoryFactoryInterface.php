<?php

namespace Doctrine\ORMLess;

interface ObjectRepositoryFactoryInterface
{
    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className);
}
