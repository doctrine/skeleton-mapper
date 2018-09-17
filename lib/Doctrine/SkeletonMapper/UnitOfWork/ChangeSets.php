<?php

namespace Doctrine\SkeletonMapper\UnitOfWork;

class ChangeSets
{
    /**
     * @var array
     */
    private $changeSets = array();

    /**
     * @param object                                     $object
     * @param \Doctrine\SkeletonMapper\UnitOfWork\Change $change
     */
    public function addObjectChange($object, Change $change)
    {
        $this->getObjectChangeSet($object)->addChange($change);
    }

    /**
     * @param object $object
     *
     * @return \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet
     */
    public function getObjectChangeSet($object)
    {
        $oid = spl_object_hash($object);

        if (!isset($this->changeSets[$oid])) {
            $this->changeSets[$oid] = new ChangeSet($object);
        }

        return $this->changeSets[$oid];
    }
}
