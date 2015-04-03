<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\SkeletonMapper\Collections;

use Doctrine\Common\Collections\Collection;

/**
 * A PersistentCollection represents a collection of elements that have persistent state.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class PersistentCollection implements Collection
{
    /**
     * @var array
     */
    private $snapshot = array();

    /**
     * @var boolean
     */
    private $isDirty = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $collection;

    /**
     * @param \Doctrine\Common\Collections\Collection $coll
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function isDirty()
    {
        return $this->isDirty;
    }

    public function takeSnapshot()
    {
        if ($this->snapshot) {
            return;
        }

        $this->snapshot = $this->collection->toArray();
        $this->isDirty = false;
    }

    public function clearSnapshot()
    {
        $this->snapshot = array();
        $this->isDirty = $this->count() ? true : false;
    }

    public function getSnapshot()
    {
        return $this->snapshot;
    }

    public function getDeleteDiff()
    {
        return array_udiff_assoc(
            $this->snapshot,
            $this->collection->toArray(),
            function ($a, $b) { return $a === $b ? 0 : 1; }
        );
    }

    public function getInsertDiff()
    {
        return array_udiff_assoc(
            $this->collection->toArray(),
            $this->snapshot,
            function ($a, $b) { return $a === $b ? 0 : 1; }
        );
    }

    public function first()
    {
        return $this->collection->first();
    }

    public function last()
    {
        return $this->collection->last();
    }

    public function remove($key)
    {
        return $this->collection->remove($key);
    }

    public function removeElement($element)
    {
        $removed = $this->collection->removeElement($element);

        if ( ! $removed) {
            return $removed;
        }

        return $removed;
    }

    public function containsKey($key)
    {
        return $this->collection->containsKey($key);
    }

    public function contains($element)
    {
        return $this->collection->contains($element);
    }

    public function exists(\Closure $p)
    {
        return $this->collection->exists($p);
    }

    public function indexOf($element)
    {
        return $this->collection->indexOf($element);
    }

    public function get($key)
    {
        return $this->collection->get($key);
    }

    public function getKeys()
    {
        return $this->collection->getKeys();
    }

    public function getValues()
    {
        return $this->collection->getValues();
    }

    public function count()
    {
        return $this->collection->count();
    }

    public function set($key, $value)
    {
        $this->collection->set($key, $value);
    }

    public function add($value)
    {
        $this->takeSnapshot();
        $this->collection->add($value);
        return true;
    }

    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    public function map(\Closure $func)
    {
        return $this->collection->map($func);
    }

    public function filter(\Closure $p)
    {
        return $this->collection->filter($p);
    }

    public function forAll(\Closure $p)
    {
        return $this->collection->forAll($p);
    }

    public function partition(\Closure $p)
    {
        return $this->collection->partition($p);
    }

    public function toArray()
    {
        return $this->collection->toArray();
    }

    public function clear()
    {
        $this->collection->clear();
    }

    public function slice($offset, $length = null)
    {
        return $this->collection->slice($offset, $length);
    }

    public function __sleep()
    {
        return array('coll');
    }

    /* ArrayAccess implementation */

    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            return $this->add($value);
        }

        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    public function key()
    {
        return $this->collection->key();
    }

    public function current()
    {
        return $this->collection->current();
    }

    public function next()
    {
        return $this->collection->next();
    }

    public function unwrap()
    {
        return $this->collection;
    }

    public function __clone()
    {
        $this->collection = clone $this->collection;
        $this->snapshot = array();
    }
}
