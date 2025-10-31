<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\PasswordHistory;
use BizUserBundle\Repository\PasswordHistoryRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * 测试 PasswordHistoryRepository 类
 *
 * @internal
 */
#[CoversClass(PasswordHistoryRepository::class)]
#[RunTestsInSeparateProcesses]
final class PasswordHistoryRepositoryTest extends AbstractRepositoryTestCase
{
    private PasswordHistoryRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(PasswordHistoryRepository::class);
    }

    public function testFindLatestPasswordHistoryWithNonExistentUser(): void
    {
        $result = $this->repository->findLatestPasswordHistory('non_existent_user_12345');

        $this->assertNull($result);
    }

    public function testPasswordHistoryEntityCanBeCreated(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('testuser');
        $history->setCiphertext('encrypted_password');

        $this->assertEquals('testuser', $history->getUsername());
        $this->assertEquals('encrypted_password', $history->getCiphertext());
    }

    public function testPasswordHistoryWithNeedReset(): void
    {
        $history = new PasswordHistory();
        $history->setNeedReset(true);

        $this->assertTrue($history->isNeedReset());
    }

    public function testPasswordHistoryWithoutNeedReset(): void
    {
        $history = new PasswordHistory();
        $history->setNeedReset(false);

        $this->assertFalse($history->isNeedReset());
    }

    public function testPasswordHistoryDefaultNeedReset(): void
    {
        $history = new PasswordHistory();

        $this->assertFalse($history->isNeedReset());
    }

    // PHPStan要求的基础仓库方法测试

    /**
     * 测试find方法查找存在的实体
     */

    /**
     * 测试findAll方法当没有记录时返回空数组
     */

    /**
     * 测试findAll方法当有记录时返回实体数组
     */

    /**
     * 测试数据库不可用时findAll方法抛出异常
     */

    /**
     * 测试findOneBy方法匹配条件时返回实体
     */

    /**
     * 测试findBy方法匹配条件时返回实体数组
     */

    /**
     * 测试findBy方法排序逻辑
     */

    /**
     * 测试findBy方法分页参数
     */

    /**
     * 测试数据库不可用时count方法抛出异常
     */

    /**
     * 测试count方法使用不存在字段时抛出异常
     */

    /**
     * 测试无效字段查询的健壮性
     */

    /**
     * 测试可空字段的IS NULL查询
     */
    public function testFindByWithNullableFieldIsNull(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_null_field_' . uniqid());
        $history->setCiphertext(null); // 设置为null
        $this->repository->save($history);

        $result = $this->repository->findBy(['ciphertext' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询
     */
    public function testCountWithNullableFieldIsNull(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_count_null_' . uniqid());
        $history->setExpireTime(null); // 设置为null
        $this->repository->save($history);

        $result = $this->repository->count(['expireTime' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试save方法
     */
    public function testSaveMethodShouldPersistEntity(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_save_method_' . uniqid());
        $history->setCiphertext('encrypted_password');

        $this->repository->save($history);

        $this->assertNotNull($history->getId());

        // 验证实体已保存
        $savedHistory = $this->repository->find($history->getId());
        $this->assertInstanceOf(PasswordHistory::class, $savedHistory);
        $this->assertEquals($history->getUsername(), $savedHistory->getUsername());
    }

    /**
     * 测试save方法不刷新
     */
    public function testSaveMethodWithoutFlush(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_save_no_flush_' . uniqid());
        $history->setCiphertext('encrypted_password');

        $this->repository->save($history, false);

        // 手动刷新
        self::getService(EntityManagerInterface::class)->flush();

        $this->assertNotNull($history->getId());
    }

    /**
     * 测试remove方法
     */
    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_remove_method_' . uniqid());
        $history->setCiphertext('encrypted_password');
        $this->repository->save($history);

        $historyId = $history->getId();
        $this->repository->remove($history);

        // 验证实体已删除
        $deletedHistory = $this->repository->find($historyId);
        $this->assertNull($deletedHistory);
    }

    /**
     * 测试remove方法不刷新
     */
    public function testRemoveMethodWithoutFlush(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_remove_no_flush_' . uniqid());
        $history->setCiphertext('encrypted_password');
        $this->repository->save($history);

        $historyId = $history->getId();
        $this->repository->remove($history, false);

        // 手动刷新
        self::getService(EntityManagerInterface::class)->flush();

        // 验证实体已删除
        $deletedHistory = $this->repository->find($historyId);
        $this->assertNull($deletedHistory);
    }

    /**
     * 测试findLatestPasswordHistory使用存在的用户名
     */
    public function testFindLatestPasswordHistoryWithExistingUser(): void
    {
        $username = 'test_latest_user_' . uniqid();

        // 创建多个密码历史记录
        $history1 = new PasswordHistory();
        $history1->setUsername($username);
        $history1->setCiphertext('old_password');
        $this->repository->save($history1);

        // 稍后创建的记录
        sleep(1);
        $history2 = new PasswordHistory();
        $history2->setUsername($username);
        $history2->setCiphertext('new_password');
        $this->repository->save($history2);

        $result = $this->repository->findLatestPasswordHistory($username);

        $this->assertInstanceOf(PasswordHistory::class, $result);
        $this->assertEquals('new_password', $result->getCiphertext());
    }

    /**
     * 测试更多可空字段的IS NULL查询 - userId字段
     */
    public function testFindByWithUserIdFieldIsNull(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_null_userid_' . uniqid());
        $history->setUserId(null); // 设置为null
        $this->repository->save($history);

        $result = $this->repository->findBy(['userId' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试更多可空字段的count IS NULL查询 - userId字段
     */
    public function testCountWithUserIdFieldIsNull(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('test_count_userid_null_' . uniqid());
        $history->setUserId(null); // 设置为null
        $this->repository->save($history);

        $result = $this->repository->count(['userId' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试findOneBy方法对null字段的查询
     */

    /**
     * 测试findBy方法对null字段的查询返回所有匹配记录
     */

    /**
     * 测试count方法对null字段的查询返回正确数量
     */

    /**
     * 测试findOneBy方法的排序逻辑
     */

    /**
     * @return ServiceEntityRepository<PasswordHistory>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): PasswordHistory
    {
        $history = new PasswordHistory();
        $history->setUsername('test_user_' . uniqid());
        $history->setCiphertext('hashed_password_' . uniqid());
        $history->setUserId('user_id_' . uniqid());
        $history->setExpireTime(new \DateTimeImmutable('+1 day'));

        return $history;
    }
}
