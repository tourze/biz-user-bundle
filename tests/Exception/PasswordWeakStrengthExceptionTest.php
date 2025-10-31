<?php

namespace BizUserBundle\Tests\Exception;

use BizUserBundle\Exception\PasswordWeakStrengthException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(PasswordWeakStrengthException::class)]
final class PasswordWeakStrengthExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常消息正确传递
     */
    public function testExceptionMessage(): void
    {
        $message = '密码不符合安全要求';
        $exception = new PasswordWeakStrengthException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertNotNull($exception);
    }
}
