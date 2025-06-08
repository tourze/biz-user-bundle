<?php

namespace BizUserBundle\Tests\Exception;

use BizUserBundle\Exception\UsernameInvalidException;
use PHPUnit\Framework\TestCase;

class UsernameInvalidExceptionTest extends TestCase
{
    /**
     * 测试异常消息正确传递
     */
    public function testExceptionMessage(): void
    {
        $message = '用户名不合法';
        $exception = new UsernameInvalidException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
