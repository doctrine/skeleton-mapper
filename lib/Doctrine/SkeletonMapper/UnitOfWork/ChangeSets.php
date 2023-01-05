<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\UnitOfWork;

use function spl_object_hash;

class ChangeSets
{
    /** @var ChangeSet[] */
    private array $changeSets = [];

    public function addObjectChange(object $object, Change $change): void
    {
        $this->getObjectChangeSet($object)->addChange($change);
    }

    public function getObjectChangeSet(object $object): ChangeSet
    {
        $oid = spl_object_hash($object);

        if (! isset($this->changeSets[$oid])) {
            $this->changeSets[$oid] = new ChangeSet($object);
        }

        return $this->changeSets[$oid];
    }
}
