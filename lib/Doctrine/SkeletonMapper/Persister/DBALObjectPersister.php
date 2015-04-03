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

use Doctrine\DBAL\Connection;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class DBALObjectPersister extends BasicObjectPersister
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\DBAL\Connection                       $connection
     * @param string                                          $className
     * @param string                                          $tableName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Connection $connection,
        $className = null,
        $tableName = null)
    {
        parent::__construct($objectManager, $className);
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    public function persistObject($object)
    {
        $data = $this->preparePersistChangeSet($object);

        $this->connection->insert($this->getTableName(), $data);

        $class = $this->objectManager->getClassMetadata(get_class($object));

        if (!isset($data[$class->identifier[0]])) {
            $data[$class->identifier[0]] = $this->connection->lastInsertId();
        }

        return $data;
    }

    public function updateObject($object, array $changeSet)
    {
        $data = $this->prepareUpdateChangeSet($object, $changeSet);

        $this->connection->update(
            $this->getTableName(),
            $data,
            $this->getObjectIdentifier($object)
        );

        return $data;
    }

    public function removeObject($object)
    {
        $this->connection->delete(
            $this->getTableName(),
            $this->getObjectIdentifier($object)
        );
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    protected function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
