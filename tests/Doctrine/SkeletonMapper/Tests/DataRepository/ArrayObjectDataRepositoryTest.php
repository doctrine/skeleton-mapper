<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\ArrayObjectDataRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ArrayObjectDataRepositoryTest extends TestCase
{
    private $objectManager;
    private $objects;
    private $objectDataRepository;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->getMock();

        $this->objects = new ArrayCollection(array(
            array(
                'username' => 'jwage'
            )
        ));

        $this->objectDataRepository = new ArrayObjectDataRepository(
            $this->objectManager,
            $this->objects,
            'TestClassName'
        );
    }

    public function testFindAll()
    {
        $this->assertSame(array(array('username' => 'jwage')), $this->objectDataRepository->findAll());
    }

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
