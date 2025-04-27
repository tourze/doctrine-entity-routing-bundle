<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle;

class DoctrineEntityRoutingBundleTest extends TestCase
{
    public function testBundleCreation(): void
    {
        $bundle = new DoctrineEntityRoutingBundle();
        $this->assertInstanceOf(DoctrineEntityRoutingBundle::class, $bundle);
    }
}
