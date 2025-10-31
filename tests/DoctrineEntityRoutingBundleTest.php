<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityRoutingBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineEntityRoutingBundle::class)]
#[RunTestsInSeparateProcesses]
final class DoctrineEntityRoutingBundleTest extends AbstractBundleTestCase
{
}
