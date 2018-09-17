<?php

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use GuzzleHttp\Client;

/**
 * Base class for HTTP object data repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class HttpObjectDataRepository extends BasicObjectDataRepository
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

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function findAll()
    {
        return $this->client->get($this->url)->json();
    }

    /**
     * TODO: This method needs to be finished.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->client->get($this->url.'?'.http_build_query($criteria))->json();
    }

    public function findOneBy(array $criteria)
    {
        return current($this->findBy($criteria)) ?: null;
    }
}
