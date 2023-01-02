<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests;

use Doctrine\SkeletonMapper\ObjectFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

/** @group unit */
class ObjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new ObjectFactoryStub();
        $object  = $factory->create(stdClass::class);
        self::assertInstanceOf(stdClass::class, $object);
    }

    public function testCreateWithReflectionMethod(): void
    {
        $factory = new ObjectFactoryReflectionMethodStub();
        $object  = $factory->create(stdClass::class);
        self::assertInstanceOf(stdClass::class, $object);
    }
}

class ObjectFactoryStub extends ObjectFactory
{
}

class ObjectFactoryReflectionMethodStub extends ObjectFactory
{
    protected function isReflectionMethodAvailable(): bool
    {
        return true;
    }
}
