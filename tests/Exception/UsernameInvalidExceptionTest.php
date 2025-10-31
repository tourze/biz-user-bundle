<?php

namespace BizUserBundle\Tests\Exception;

use BizUserBundle\Exception\UsernameInvalidException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(UsernameInvalidException::class)]
final class UsernameInvalidExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常消息正确传递
     */
    public function testExceptionMessage(): void
    {
        $message = '用户名不合法';
        $exception = new UsernameInvalidException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertNotNull($exception);
    }
}
