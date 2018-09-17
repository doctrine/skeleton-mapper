<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\SkeletonMapper\DataRepository\CacheObjectDataRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class CacheObjectDataRepositoryTest extends TestCase
{
    private $objectManager;
    private $cache;
    private $objectDataRepository;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->getMock();

        $this->cache = new ArrayCache();
        $this->cache->save(1, array('username' => 'jwage'));

        $this->objectDataRepository = new CacheObjectDataRepository(
            $this->objectManager,
            $this->cache,
            'TestClassName'
        );
    }

    public function testFind()
    {
        $this->assertSame(
            array('username' => 'jwage'),
            $this->objectDataRepository->find(array('id' => 1))
        );
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Not implemented.
     */
    public function testFindAll()
    {
        $this->assertSame(array(array('username' => 'jwage')), $this->objectDataRepository->findAll());
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Not implemented.
     */
    public function testFindBy()
    {
        $criteria = array('username' => 'jwage');
        $orderBy = array('username' => 'desc');
        $limit = 20;
        $offset = 20;

        $this->assertSame(
            array(array('username' => 'jwage')),
            $this->objectDataRepository->findBy($criteria, $orderBy, $limit, $offset)
        );
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Not implemented.
     */
    public function testFindOneBy()
    {
        $criteria = array('username' => 'jwage');
        $orderBy = array('username' => 'desc');
        $limit = 20;
        $offset = 20;

        $this->assertSame(
            array('username' => 'jwage'),
            $this->objectDataRepository->findOneBy($criteria)
        );
    }
}
