<?php

namespace Doctrine\SkeletonMapper\Collections;

use Closure;
use Doctrine\Common\Collections\AbstractLazyCollection;

class LazyCollection extends AbstractLazyCollection
{
    /**
     * @var \Closure
     */
    private $callback;

    /**
     * @param \Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCollection()
    {
        $this->initialize();
        return $this->collection;
    }

    /**
     * Initializes the collection.
     *
     * @return void
     */
    protected function doInitialize()
    {
        $this->collection = $this->callback->__invoke();
        $this->callback = null;
    }
}
