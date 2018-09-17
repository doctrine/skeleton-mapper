<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\HttpObjectDataRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class HttpObjectDataRepositoryTest extends TestCase
{
    private $objectManager;
    private $client;
    private $objectDataRepository;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->getMock();

        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectDataRepository = new TestHttpObjectDataRepository(
            $this->objectManager,
            $this->client,
            'TestClassName',
            'http://localhost/users'
        );
    }

    public function testGetUrl()
    {
        $this->assertSame(
            'http://localhost/users',
            $this->objectDataRepository->getUrl()
        );
    }

    public function testGetClient()
    {
        $this->assertSame(
            $this->client,
            $this->objectDataRepository->getClient()
        );
    }

    public function testFindAll()
    {
        $results = array(
            array('username' => 'jwage'),
        );

        $response = $this->getMockBuilder('GuzzleHttp\Message\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($results));

        $this->client->expects($this->once())
            ->method('get')
            ->with('http://localhost/users')
            ->will($this->returnValue($response));

        $this->assertSame($results, $this->objectDataRepository->findAll());
    }

    public function testFindBy()
    {
        $criteria = array('username' => 'jwage');
        $orderBy = array('username' => 'desc');
        $limit = 20;
        $offset = 20;

        $results = array(
            array('username' => 'jwage'),
        );

        $response = $this->getMockBuilder('GuzzleHttp\Message\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($results));

        $this->client->expects($this->once())
            ->method('get')
            ->with('http://localhost/users?username=jwage')
            ->will($this->returnValue($response));

        $this->assertSame($results, $this->objectDataRepository->findBy($criteria, $orderBy, $limit, $offset));
    }

    public function testFindOneBy()
    {
        $criteria = array('username' => 'jwage');

        $results = array(
            array('username' => 'jwage'),
        );

        $response = $this->getMockBuilder('GuzzleHttp\Message\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($results));

        $this->client->expects($this->once())
            ->method('get')
            ->with('http://localhost/users?username=jwage')
            ->will($this->returnValue($response));

        $this->assertSame($results[0], $this->objectDataRepository->findOneBy($criteria));
    }
}

class TestHttpObjectDataRepository extends HttpObjectDataRepository
{
}
