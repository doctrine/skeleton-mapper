<?php

namespace Doctrine\SkeletonMapper\Tests;

interface DataTesterInterface
{
    public function find($id);
    public function set($id, $key, $value);
    public function count();
}
