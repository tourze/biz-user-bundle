<?php

declare(strict_types=1);

namespace BizUserBundle\Tests;

use BizUserBundle\BizUserBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(BizUserBundle::class)]
#[RunTestsInSeparateProcesses]
final class BizUserBundleTest extends AbstractBundleTestCase
{
}
