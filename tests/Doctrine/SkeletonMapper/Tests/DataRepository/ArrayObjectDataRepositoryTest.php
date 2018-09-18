<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\DataRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\ArrayObjectDataRepository;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ArrayObjectDataRepositoryTest extends TestCase
{
    /** @var ObjectManagerInterface|MockObject */
    private $objectManager;

    /** @var object[]|ArrayCollection */
    private $objects;

    /** @var ArrayObjectDataRepository */
    private $objectDataRepository;

    public function testFindAll() : void
    {
        self::assertSame([['username' => 'jwage']], $this->objectDataRepository->findAll());
    }

    public function testFindBy() : void
    {
        $criteria = ['username' => 'jwage'];
        $orderBy  = ['username' => 'desc'];
        $limit    = 20;
        $offset   = 20;

        self::assertSame(
            [['username' => 'jwage']],
            $this->objectDataRepository->findBy($criteria, $orderBy, $limit, $offset)
        );
    }

    public function testFindOneBy() : void
    {
        $criteria = ['username' => 'jwage'];
        $orderBy  = ['username' => 'desc'];
        $limit    = 20;
        $offset   = 20;

        self::assertSame(
            ['username' => 'jwage'],
            $this->objectDataRepository->findOneBy($criteria)
        );
    }

    protected function setUp() : void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->objects = new ArrayCollection([
            ['username' => 'jwage'],
        ]);

        $this->objectDataRepository = new ArrayObjectDataRepository(
            $this->objectManager,
            $this->objects,
            'TestClassName'
        );
    }
}
