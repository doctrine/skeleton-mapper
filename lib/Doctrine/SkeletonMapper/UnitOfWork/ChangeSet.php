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

namespace Doctrine\SkeletonMapper\UnitOfWork;

class ChangeSet
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $changes = array();

    /**
     * @param object $object
     * @param array  $changes
     */
    public function __construct($object, array $changes = array())
    {
        $this->object = $object;
        $this->changes = $changes;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param \Doctrine\SkeletonMapper\UnitOfWork\Change $change
     */
    public function addChange(Change $change)
    {
        $this->changes[$change->getPropertyName()] = $change;
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasChangedField($fieldName)
    {
        return isset($this->changes[$fieldName]);
    }

    /**
     * @param string $fieldName
     *
     * @return \Doctrine\SkeletonMapper\UnitOfWork\Change
     */
    public function getFieldChange($fieldName)
    {
        return isset($this->changes[$fieldName]) ? $this->changes[$fieldName] : null;
    }
}
