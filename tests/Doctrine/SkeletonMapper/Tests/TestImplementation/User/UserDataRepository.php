<?php

namespace Doctrine\SkeletonMapper\Tests\TestImplementation\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Repository\ObjectDataRepository;

class UserDataRepository extends ObjectDataRepository
{
    private $users;

    public function __construct(ArrayCollection $users)
    {
        $this->users = $users;
    }

    public function find($id)
    {
        return isset($this->users[$id]) ? $this->users[$id] : null;
    }

    public function findByObject($object)
    {
        return $this->find($object->getId());
    }

    public function findAll()
    {
        return $this->users;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $users = array();

        foreach ($this->users as $user) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($user[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function findOneBy(array $criteria)
    {
        foreach ($this->users as $user) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($user[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                return $user;
            }
        }
    }
}
