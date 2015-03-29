<?php

namespace Doctrine\ORMLess;

interface ObjectFactoryInterface
{
    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className);
}
