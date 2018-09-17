<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
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
    protected $url;

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
        $data = $this->preparePersistChangeSet($object);

        return $this->client->post($this->url, array('body' => $data))->json();
    }

    public function updateObject($object, ChangeSet $changeSet)
    {
        $data = $this->prepareUpdateChangeSet($object, $changeSet);

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
}
