<?php

namespace Doctrine\SkeletonMapper\Tests;

interface UsersTesterInterface
{
    public function find($id);
    public function set($id, $key, $value);
    public function count();
}
