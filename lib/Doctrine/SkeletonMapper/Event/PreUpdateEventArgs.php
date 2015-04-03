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

namespace Doctrine\SkeletonMapper\Event;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Class that holds event arguments for a preUpdate event.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class PreUpdateEventArgs extends LifecycleEventArgs
{
    /**
     * @var array
     */
    private $objectChangeSet;

    /**
     * Constructor.
     *
     * @param object                                          $object
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet   $changeSet
     */
    public function __construct(
        $object,
        ObjectManagerInterface $objectManager,
        ChangeSet $changeSet)
    {
        parent::__construct($object, $objectManager);
        $this->objectChangeSet = $changeSet;
    }

    /**
     * Retrieves the object changeset.
     *
     * @return array
     */
    public function getObjectChangeSet()
    {
        return $this->objectChangeSet;
    }

    /**
     * Checks if field has a changeset.
     *
     * @param string $field
     *
     * @return bool
     */
    public function hasChangedField($field)
    {
        return $this->objectChangeSet->hasChangedField($field);
    }

    /**
     * Gets the old value of the changeset of the changed field.
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getOldValue($field)
    {
        if ($change = $this->objectChangeSet->getFieldChange($field)) {
            return $change->getOldValue();
        }
    }

    /**
     * Gets the new value of the changeset of the changed field.
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getNewValue($field)
    {
        if ($change = $this->objectChangeSet->getFieldChange($field)) {
            return $change->getNewValue();
        }
    }

    /**
     * Sets the new value of this field.
     *
     * @param string $field
     * @param mixed  $value
     */
    public function setNewValue($field, $value)
    {
        if ($change = $this->objectChangeSet->getFieldChange($field)) {
            $change->setNewValue($value);
        }
    }
}
