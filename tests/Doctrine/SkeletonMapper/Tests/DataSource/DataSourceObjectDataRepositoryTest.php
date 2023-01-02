<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\DataSource;

use Doctrine\SkeletonMapper\DataSource\DataSource;
use Doctrine\SkeletonMapper\DataSource\DataSourceObjectDataRepository;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class DataSourceObjectDataRepositoryTest extends TestCase
{
    /** @var ObjectManagerInterface|MockObject */
    private $objectManager;

    /** @var DataSource|MockObject */
    private $dataSource;

    /** @var DataSourceObjectDataRepository */
    private $dataSourceObjectDataRepository;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->dataSource    = $this->createMock(DataSource::class);

        $this->dataSourceObjectDataRepository = new DataSourceObjectDataRepository(
            $this->objectManager,
            $this->dataSource,
            stdClass::class,
        );
    }

    public function testFindAll(): void
    {
        $rows = [
            ['row' => 1],
            ['row' => 2],
        ];

        $this->dataSource->expects(self::once())
            ->method('getSourceRows')
            ->willReturn($rows);

        self::assertEquals($rows, $this->dataSourceObjectDataRepository->findAll());
    }

    public function testFindByCriteria(): void
    {
        $rows = [
            ['username' => 'jwage'],
            ['username' => 'ocramius'],
        ];

        $this->dataSource->expects(self::once())
            ->method('getSourceRows')
            ->willReturn($rows);

        self::assertEquals([
            ['username' => 'ocramius'],
        ], $this->dataSourceObjectDataRepository->findBy(['username' => 'ocramius']));
    }

    public function testFindByOrderBy(): void
    {
        $rows = [
            ['username' => 'jwage'],
            ['username' => 'ocramius'],
        ];

        $this->dataSource->expects(self::once())
            ->method('getSourceRows')
            ->willReturn($rows);

        self::assertEquals([
            ['username' => 'ocramius'],
            ['username' => 'jwage'],
        ], $this->dataSourceObjectDataRepository->findBy([], ['username' => 'desc']));
    }

    public function testFindByLimitAndOffset(): void
    {
        $rows = [
            ['username' => 'jwage'],
            ['username' => 'ocramius'],
            ['username' => 'andreas'],
        ];

        $this->dataSource->expects(self::once())
            ->method('getSourceRows')
            ->willReturn($rows);

        self::assertEquals([
            ['username' => 'ocramius'],
            ['username' => 'andreas'],
        ], $this->dataSourceObjectDataRepository->findBy([], [], 2, 1));
    }

    public function testFindByLimit(): void
    {
        $rows = [
            ['username' => 'jwage'],
            ['username' => 'ocramius'],
            ['username' => 'andreas'],
        ];

        $this->dataSource->expects(self::once())
            ->method('getSourceRows')
            ->willReturn($rows);

        self::assertEquals([
            ['username' => 'jwage'],
        ], $this->dataSourceObjectDataRepository->findBy([], [], 1));
    }

    public function testFindByOffset(): void
    {
        $rows = [
            ['username' => 'jwage'],
            ['username' => 'ocramius'],
            ['username' => 'andreas'],
        ];

        $this->dataSource->expects(self::once())
            ->method('getSourceRows')
            ->willReturn($rows);

        self::assertEquals([
            ['username' => 'ocramius'],
            ['username' => 'andreas'],
        ], $this->dataSourceObjectDataRepository->findBy([], [], null, 1));
    }
}
