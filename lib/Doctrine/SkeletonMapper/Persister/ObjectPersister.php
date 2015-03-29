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

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\ObjectIdentityMap;

/**
 * Base class for object persisters to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class ObjectPersister implements ObjectPersisterInterface
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectIdentityMap
     */
    protected $objectIdentityMap;

    /**
     * @var array
     */
    protected $objectsToPersist = array();

    /**
     * @var array
     */
    protected $objectsToUpdate = array();

    /**
     * @var array
     */
    protected $objectsToRemove = array();

    /**
     * @param \Doctrine\SkeletonMapper\ObjectIdentityMap $objectIdentityMap
     */
    public function __construct(ObjectIdentityMap $objectIdentityMap)
    {
        $this->objectIdentityMap = $objectIdentityMap;
    }

    /**
     * @param object $object
     */
    public function persist($object)
    {
        $this->objectsToPersist[] = $object;
    }

    /**
     * @param object $object
     */
    public function update($object)
    {
        $this->objectsToUpdate[] = $object;
    }

    /**
     * @param object $object
     */
    public function remove($object)
    {
        $this->objectsToRemove[] = $object;
    }

    /**
     */
    public function clear()
    {
        $this->objectsToPersist = array();
        $this->objectsToRemove = array();
    }

    /**
     */
    public function commit()
    {
        foreach ($this->objectsToPersist as $object) {
            $objectData = $this->persistObject($object);

            $this->objectIdentityMap->addToIdentityMap($object, $objectData);
        }

        foreach ($this->objectsToUpdate as $object) {
            $this->updateObject($object);
        }

        foreach ($this->objectsToRemove as $object) {
            $this->removeObject($object);

            $this->objectIdentityMap->detach($object);
        }

        $this->clear();
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForPersist($object)
    {
        return in_array($object, $this->objectsToPersist);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForUpdate($object)
    {
        return in_array($object, $this->objectsToUpdate);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isScheduledForRemove($object)
    {
        return in_array($object, $this->objectsToRemove);
    }
}
