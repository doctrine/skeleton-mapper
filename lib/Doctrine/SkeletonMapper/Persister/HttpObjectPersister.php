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
use GuzzleHttp\Client;

class HttpObjectPersister extends BasicObjectPersister
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \GuzzleHttp\Client                              $client
     * @param string                                          $className
     * @param string                                          $url
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Client $client,
        $className = null,
        $url = null)
    {
        parent::__construct($objectManager, $className);
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function persistObject($object)
    {
        $data = $this->prepareChangeSet($object);

        return $this->client->post($this->url, array('body' => $data))->json();
    }

    public function updateObject($object, array $changeSet)
    {
        $data = $this->prepareChangeSet($object, $changeSet);

        $identifier = $this->getObjectIdentifier($object);
        $class = $this->objectManager->getClassMetadata(get_class($object));
        $url = sprintf('%s/%s', $this->url, $identifier[$class->identifier[0]]);

        return $this->client->put($url, array('body' => $data))->json();
    }

    public function removeObject($object)
    {
        $identifier = $this->getObjectIdentifier($object);
        $class = $this->objectManager->getClassMetadata(get_class($object));
        $url = sprintf('%s/%s', $this->url, $identifier[$class->identifier[0]]);

        $this->client->delete($url);
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
