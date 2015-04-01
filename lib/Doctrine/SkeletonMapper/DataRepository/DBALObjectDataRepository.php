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

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\DBAL\Connection;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Base class for DBAL object data repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class DBALObjectDataRepository extends BasicObjectDataRepository
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

        $sqlParts = array();
        $sqlParts[] = sprintf('SELECT * FROM %s WHERE %s', $this->getTableName(), implode(' AND ', $where));

        if ($orderBy !== null) {
            $orderBySqlParts = array();
            foreach ($orderBy as $fieldName => $orientation) {
                $orderBySqlParts[] = sprintf('%s %s', $fieldName, $orientation);
            }

            $sqlParts[] = 'ORDER BY '.implode(', ', $orderBySqlParts);
        }

        if ($limit !== null) {
            $sqlParts[] = sprintf('LIMIT %s', $limit);
        }

        if ($offset !== null) {
            $sqlParts[] = sprintf('OFFSET %s', $offset);
        }

        $sql = implode(' ', $sqlParts);

        return $this->connection
            ->executeQuery($sql, $params)
            ->fetchAll();
    }

    public function findOneBy(array $criteria)
    {
        return current($this->findBy($criteria)) ?: null;
    }
}
