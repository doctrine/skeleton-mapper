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

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use MongoCollection;

abstract class MongoDBObjectPersister extends ObjectPersister
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \MongoCollection
     */
    protected $mongoCollection;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \MongoCollection                                $mongoCollection
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MongoCollection $mongoCollection)
    {
        $this->objectManager = $objectManager;
        $this->mongoCollection = $mongoCollection;
    }

    public function persistObject($object)
    {
        $data = $this->prepareChangeSet($object);

        $this->mongoCollection->insert($data);

        return $data;
    }

    public function updateObject($object, array $changeSet)
    {
        $data = $this->prepareChangeSet($object, $changeSet);

        unset($data['_id']);

        $this->mongoCollection->update(
            $this->getObjectIdentifier($object),
            array('$set' => $data)
        );

        return $data;
    }

    public function removeObject($object)
    {
        $this->mongoCollection->remove($this->getObjectIdentifier($object));
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    private function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
