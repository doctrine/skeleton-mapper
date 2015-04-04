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

namespace Doctrine\SkeletonMapper;

/**
 * @codeCoverageIgnore
 */
final class Events
{
    /**
     * Private constructor. This class is not meant to be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * The preRemove event occurs for a given object before the respective
     * ObjectManager remove operation for that object is executed.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const preRemove = 'preRemove';

    /**
     * The postRemove event occurs for a object after the object has
     * been deleted. It will be invoked after the database delete operations.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const postRemove = 'postRemove';

    /**
     * The prePersist event occurs for a given object before the respective
     * ObjectManager persist operation for that object is executed.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const prePersist = 'prePersist';

    /**
     * The postPersist event occurs for a object after the object has
     * been made persistent.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const postPersist = 'postPersist';

    /**
     * The preUpdate event occurs before the database update operations to
     * object data.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const preUpdate = 'preUpdate';

    /**
     * The postUpdate event occurs after the database update operations to
     * object data.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const postUpdate = 'postUpdate';

    /**
     * The preLoad event occurs for a object before the object has been loaded
     * into the current ObjectManager from the database or before the refresh operation
     * has been applied to it.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const preLoad = 'preLoad';

    /**
     * The postLoad event occurs for a object after the object has been loaded
     * into the current ObjectManager from the database or after the refresh operation
     * has been applied to it.
     *
     * This is a object lifecycle event.
     *
     * @var string
     */
    const postLoad = 'postLoad';

    /**
     * The preFlush event occurs when the ObjectManager#flush() operation is invoked.
     * This event is always raised right after ObjectManager#flush() call.
     */
    const preFlush = 'preFlush';

    /**
     * The onFlush event occurs when the ObjectManager#flush() operation is invoked
     * but before any actual database operations are executed. The event is only raised if there is
     * actually something to do for the underlying UnitOfWork. If nothing needs to be done,
     * the onFlush event is not raised.
     *
     * @var string
     */
    const onFlush = 'onFlush';

    /**
     * The postFlush event occurs when the ObjectManager#flush() operation is invoked and
     * after all actual database operations are executed successfully. The event is only raised if there is
     * actually something to do for the underlying UnitOfWork. If nothing needs to be done,
     * the postFlush event is not raised. The event won't be raised if an error occurs during the
     * flush operation.
     *
     * @var string
     */
    const postFlush = 'postFlush';

    /**
     * The onClear event occurs when the ObjectManager#clear() operation is invoked,
     * after all references to objects have been removed from the unit of work.
     *
     * @var string
     */
    const onClear = 'onClear';
}
