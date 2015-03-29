<?php

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\Persistence\ObjectManager as BaseObjectManagerInterface;

interface ObjectManagerInterface extends BaseObjectManagerInterface
{
    public function update($object);
}
