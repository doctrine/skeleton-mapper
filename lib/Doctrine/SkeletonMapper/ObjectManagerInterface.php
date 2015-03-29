<?php

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\Persistence\ObjectManager as BaseObjectManagerInterface;

interface ObjectManagerInterface extends BaseObjectManagerInterface
{
	/**
	 * @param object $object
	 */
    public function update($object);
}
