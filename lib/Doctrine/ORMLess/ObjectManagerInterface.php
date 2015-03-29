<?php

namespace Doctrine\ORMLess;

use Doctrine\Common\Persistence\ObjectManager as BaseObjectManagerInterface;

interface ObjectManagerInterface extends BaseObjectManagerInterface
{
    public function update($object);
}
