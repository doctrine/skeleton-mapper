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

/**
 * Interface that object persisters must implement.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
interface ObjectPersisterInterface
{
    /**
     * Prepares an object persist changeset for persistence.
     *
     * @param object $object
     *
     * @return array
     */
    public function preparePersistChangeSet($object);

    /**
     * Prepares an object update changeset for update.
     *
     * @param object $object
     * @param array  $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet($object, array $changeSet = array());


    /**
     * Performs operation to write object to the database.
     *
     * @param object $object
     *
     * @return array $objectData
     */
    public function persistObject($object);

    /**
     * Assign identifier to object.
     *
     * @param object $object
     * @param array  $identifier
     */
    public function assignIdentifier($object, array $identifier);

    /**
     * Performs operation to update object in the database.
     *
     * @param object $object
     * @param array  $changeSet
     *
     * @return array $objectData
     */
    public function updateObject($object, array $changeSet);

    /**
     * Performs operation to remove object in the database.
     *
     * @param object $object
     */
    public function removeObject($object);
}
