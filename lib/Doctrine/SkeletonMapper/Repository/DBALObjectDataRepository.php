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

namespace Doctrine\SkeletonMapper\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Base class for DBAL object data repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class DBALObjectDataRepository implements ObjectDataRepositoryInterface
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\DBAL\Connection                       $connection
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Connection $connection)
    {
        $this->objectManager = $objectManager;
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    abstract public function getTableName();

    /**
     * @return string
     */
    abstract public function getClassName();

    public function find($id)
    {
        $identifier = $this->getIdentifier();

        $identifierValues = is_array($id) ? $id : array($id);

        $criteria = array_combine($identifier, $identifierValues);

        return $this->findOneBy($criteria);
    }

    public function findByObject($object)
    {
        return $this->find($object->id);
    }

    public function findAll()
    {
        $sql = sprintf('SELECT * FROM %s', $this->getTableName());

        return $this->connection->fetchAll($sql);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $where = array();
        $params = array();
        foreach ($criteria as $key => $value) {
            $where[] = sprintf('%s = :%s', $key, $key);
            $params[$key] = $value;
        }

        $sql = sprintf('SELECT * FROM %s WHERE %s', $this->getTableName(), implode(' AND ', $where));

        return $this->connection
            ->executeQuery($sql, $params)
            ->fetchAll();
    }

    public function findOneBy(array $criteria)
    {
        $where = array();
        $params = array();
        foreach ($criteria as $key => $value) {
            $where[] = sprintf('%s = :%s', $key, $key);
            $params[$key] = $value;
        }

        $sql = sprintf('SELECT * FROM %s WHERE %s', $this->getTableName(), implode(' AND ', $where));

        return $this->connection
            ->executeQuery($sql, $params)
            ->fetch() ?: null;
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    private function getIdentifier()
    {
        return $this->objectManager
            ->getClassMetadata($this->getClassName())
            ->getIdentifier();
    }
}
