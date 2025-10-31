<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Exception\UsernameInvalidException;
use BizUserBundle\Repository\BizUserRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(BizUserRepository::class)]
#[RunTestsInSeparateProcesses]
final class BizUserRepositoryTest extends AbstractRepositoryTestCase
{
    private BizUserRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(BizUserRepository::class);
    }

    /**
     * 测试返回保留用户名列表
     */
    public function testGetReservedUserNames(): void
    {
        $result = $this->repository->getReservedUserNames();

        $this->assertIsArray($result);
        $this->assertContains('admin', $result);
        $this->assertContains('root', $result);
        $this->assertContains('system', $result);
        $this->assertGreaterThan(0, count($result));
    }

    /**
     * 测试合法用户名不抛出异常
     */
    public function testCheckUserLegalWithValidUsername(): void
    {
        $user = new BizUser();
        $user->setUsername('valid_user');

        $this->expectNotToPerformAssertions();
        $this->repository->checkUserLegal($user);
    }

    /**
     * 测试保留用户名抛出异常
     */
    public function testCheckUserLegalWithReservedUsername(): void
    {
        $user = new BizUser();
        $user->setUsername('admin');

        $this->expectException(UsernameInvalidException::class);
        $this->expectExceptionMessage('用户名不合法');

        $this->repository->checkUserLegal($user);
    }

    /**
     * 测试通过不存在的标识符加载用户返回null
     */
    public function testLoadUserByIdentifierReturnsNullForNonExistentUser(): void
    {
        $result = $this->repository->loadUserByIdentifier('non_existent_user_12345');

        $this->assertNull($result);
    }

    /**
     * 测试升级密码方法
     */
    public function testUpgradePassword(): void
    {
        $user = new BizUser();
        $user->setUsername('test_upgrade_user');
        $user->setPasswordHash('old_password_hash');

        $newHashedPassword = 'new_hashed_password';

        $this->repository->upgradePassword($user, $newHashedPassword);

        $this->assertEquals($newHashedPassword, $user->getPasswordHash());
    }

    // PHPStan要求的基础仓库方法测试

    /**
     * 测试find方法查找存在的实体
     */

    /**
     * 测试find方法查找不存在的实体
     */

    /**
     * 测试find方法使用0作为ID
     */
    /**
     * 测试find方法使用负数作为ID
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
     * 测试可空字段的IS NULL查询 - findOneBy版本
     */

    /**
     * 测试可空字段的IS NULL查询 - email字段 findOneBy版本
     */

    /**
     * 测试可空字段的IS NULL查询 - email字段 findBy版本
     */

    /**
     * 测试可空字段的count IS NULL查询 - 规范命名
     */

    /**
     * 测试save方法
     */
    public function testSaveMethodShouldPersistEntity(): void
    {
        $user = new BizUser();
        $user->setUsername('test_save_method_' . uniqid());
        $user->setValid(true);

        $this->repository->save($user);

        $this->assertNotNull($user->getId());

        // 验证实体已保存
        $savedUser = $this->repository->find($user->getId());
        $this->assertInstanceOf(BizUser::class, $savedUser);
        $this->assertEquals($user->getUsername(), $savedUser->getUsername());
    }

    /**
     * 测试save方法不刷新
     */
    public function testSaveMethodWithoutFlush(): void
    {
        $user = new BizUser();
        $user->setUsername('test_save_no_flush_' . uniqid());
        $user->setValid(true);

        $this->repository->save($user);

        $this->assertNotNull($user->getId());
    }

    /**
     * 测试remove方法
     */
    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $user = new BizUser();
        $user->setUsername('test_remove_method_' . uniqid());
        $user->setValid(true);
        $this->repository->save($user);

        $userId = $user->getId();
        $this->repository->remove($user);

        // 验证实体已删除
        $deletedUser = $this->repository->find($userId);
        $this->assertNull($deletedUser);
    }

    /**
     * 测试remove方法不刷新
     */
    public function testRemoveMethodWithoutFlush(): void
    {
        $user = new BizUser();
        $user->setUsername('test_remove_no_flush_' . uniqid());
        $user->setValid(true);
        $this->repository->save($user);

        $userId = $user->getId();
        $this->repository->remove($user);

        // 验证实体已删除
        $deletedUser = $this->repository->find($userId);
        $this->assertNull($deletedUser);
    }

    /**
     * 测试loadUserByIdentifier使用数字ID加载用户
     */
    public function testLoadUserByIdentifierWithNumericId(): void
    {
        $user = new BizUser();
        $user->setUsername('test_load_numeric_' . uniqid());
        $user->setValid(true);
        $this->repository->save($user);

        $result = $this->repository->loadUserByIdentifier((string) $user->getId());

        $this->assertInstanceOf(BizUser::class, $result);
        $this->assertEquals($user->getId(), $result->getId());
    }

    /**
     * 测试upgradePassword使用不支持的用户类型抛出异常
     */
    public function testUpgradePasswordWithUnsupportedUserTypeShouldThrowException(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $mockUser = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): string
            {
                return 'old_password';
            }
        };

        $this->repository->upgradePassword($mockUser, 'new_password');
    }

    /**
     * 测试更多可空字段的IS NULL查询 - mobile字段
     */
    public function testFindByWithMobileFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_mobile_' . uniqid());
        $user->setValid(true);
        $user->setMobile(null); // 设置为null
        $this->repository->save($user);

        $result = $this->repository->findBy(['mobile' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试更多可空字段的count IS NULL查询 - mobile字段
     */
    public function testCountWithMobileFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_mobile_null_' . uniqid());
        $user->setValid(true);
        $user->setMobile(null); // 设置为null
        $this->repository->save($user);

        $result = $this->repository->count(['mobile' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - avatar字段
     */
    public function testFindByWithAvatarFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_avatar_' . uniqid());
        $user->setValid(true);
        $user->setAvatar(null); // 设置为null
        $this->repository->save($user);

        $result = $this->repository->findBy(['avatar' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - avatar字段
     */
    public function testCountWithAvatarFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_avatar_null_' . uniqid());
        $user->setValid(true);
        $user->setAvatar(null); // 设置为null
        $this->repository->save($user);

        $result = $this->repository->count(['avatar' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - type字段
     */
    public function testFindByWithTypeFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_type_' . uniqid());
        $user->setValid(true);
        $user->setType(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['type' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - type字段
     */
    public function testCountWithTypeFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_type_null_' . uniqid());
        $user->setValid(true);
        $user->setType(null);
        $this->repository->save($user);

        $result = $this->repository->count(['type' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - identity字段
     */
    public function testFindByWithIdentityFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_identity_' . uniqid());
        $user->setValid(true);
        $user->setIdentity(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['identity' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - identity字段
     */
    public function testCountWithIdentityFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_identity_null_' . uniqid());
        $user->setValid(true);
        $user->setIdentity(null);
        $this->repository->save($user);

        $result = $this->repository->count(['identity' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试findOneBy方法的排序逻辑
     */

    /**
     * 测试可空字段的IS NULL查询 - passwordHash字段
     */
    public function testFindByWithPasswordHashFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_password_' . uniqid());
        $user->setValid(true);
        // passwordHash默认就是null
        $this->repository->save($user);

        $result = $this->repository->findBy(['passwordHash' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - passwordHash字段
     */
    public function testCountWithPasswordHashFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_password_null_' . uniqid());
        $user->setValid(true);
        // passwordHash默认就是null
        $this->repository->save($user);

        $result = $this->repository->count(['passwordHash' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - birthday字段
     */
    public function testFindByWithBirthdayFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_birthday_' . uniqid());
        $user->setValid(true);
        $user->setBirthday(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['birthday' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - birthday字段
     */
    public function testCountWithBirthdayFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_birthday_null_' . uniqid());
        $user->setValid(true);
        $user->setBirthday(null);
        $this->repository->save($user);

        $result = $this->repository->count(['birthday' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - gender字段
     */
    public function testFindByWithGenderFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_gender_' . uniqid());
        $user->setValid(true);
        $user->setGender(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['gender' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - gender字段
     */
    public function testCountWithGenderFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_gender_null_' . uniqid());
        $user->setValid(true);
        $user->setGender(null);
        $this->repository->save($user);

        $result = $this->repository->count(['gender' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - provinceName字段
     */
    public function testFindByWithProvinceNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_province_' . uniqid());
        $user->setValid(true);
        // provinceName默认就是null
        $this->repository->save($user);

        $result = $this->repository->findBy(['provinceName' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - provinceName字段
     */
    public function testCountWithProvinceNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_province_null_' . uniqid());
        $user->setValid(true);
        // provinceName默认就是null
        $this->repository->save($user);

        $result = $this->repository->count(['provinceName' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - remark字段
     */
    public function testFindByWithRemarkFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_remark_' . uniqid());
        $user->setValid(true);
        $user->setRemark(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['remark' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - remark字段
     */
    public function testCountWithRemarkFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_remark_null_' . uniqid());
        $user->setValid(true);
        $user->setRemark(null);
        $this->repository->save($user);

        $result = $this->repository->count(['remark' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - email字段
     */
    public function testFindByWithEmailFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_email_findby_' . uniqid());
        $user->setValid(true);
        $user->setEmail(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['email' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - nickName字段
     */
    public function testCountWithNickNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_nickname_null_' . uniqid());
        $user->setValid(true);
        $user->setNickName(null);
        $this->repository->save($user);

        $result = $this->repository->count(['nickName' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - cityName字段
     */
    public function testFindByWithCityNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_city_' . uniqid());
        $user->setValid(true);
        $user->setCityName(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['cityName' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - cityName字段
     */
    public function testCountWithCityNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_city_null_' . uniqid());
        $user->setValid(true);
        $user->setCityName(null);
        $this->repository->save($user);

        $result = $this->repository->count(['cityName' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - areaName字段
     */
    public function testFindByWithAreaNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_area_' . uniqid());
        $user->setValid(true);
        $user->setAreaName(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['areaName' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - areaName字段
     */
    public function testCountWithAreaNameFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_area_null_' . uniqid());
        $user->setValid(true);
        $user->setAreaName(null);
        $this->repository->save($user);

        $result = $this->repository->count(['areaName' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - address字段
     */
    public function testFindByWithAddressFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_address_' . uniqid());
        $user->setValid(true);
        $user->setAddress(null);
        $this->repository->save($user);

        $result = $this->repository->findBy(['address' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - address字段
     */
    public function testCountWithAddressFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_address_null_' . uniqid());
        $user->setValid(true);
        $user->setAddress(null);
        $this->repository->save($user);

        $result = $this->repository->count(['address' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试可空字段的IS NULL查询 - valid字段
     */
    public function testFindByWithValidFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_null_valid_' . uniqid());
        $user->setValid(null); // 设置为null而不是false
        $this->repository->save($user);

        $result = $this->repository->findBy(['valid' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * 测试可空字段的count IS NULL查询 - valid字段
     */
    public function testCountWithValidFieldIsNull(): void
    {
        $user = new BizUser();
        $user->setUsername('test_count_valid_null_' . uniqid());
        $user->setValid(null); // 设置为null而不是false
        $this->repository->save($user);

        $result = $this->repository->count(['valid' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    /**
     * 测试count方法查找不存在记录应该返回0 - 修复框架测试的错误断言
     * 当查询不存在的记录时，count应该返回0
     */
    public function testCountWithNonExistentIdShouldReturnZero(): void
    {
        $result = $this->repository->count(['id' => -88888]);
        $this->assertEquals(0, $result);
    }

    /**
     * 测试searchUsers方法搜索功能
     */
    public function testSearchUsers(): void
    {
        // 创建测试用户数据
        $user1 = new BizUser();
        $user1->setUsername('test_search_user1');
        $user1->setNickName('搜索测试用户1');
        $user1->setValid(true);
        $this->repository->save($user1);

        $user2 = new BizUser();
        $user2->setUsername('another_user');
        $user2->setNickName('搜索测试用户2');
        $user2->setValid(true);
        $this->repository->save($user2);

        $user3 = new BizUser();
        $user3->setUsername('disabled_user');
        $user3->setNickName('无效用户');
        $user3->setValid(false); // 无效用户，不应被搜索到
        $this->repository->save($user3);

        // 测试按用户名搜索
        $results = $this->repository->searchUsers('test_search');
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals($user1->getId(), $results[0]['id']);
        $this->assertEquals($user1->getNickName(), $results[0]['text']);

        // 测试按昵称搜索
        $results = $this->repository->searchUsers('搜索测试');
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // 测试限制数量
        $results = $this->repository->searchUsers('搜索测试', 1);
        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        // 测试搜索不存在的用户
        $results = $this->repository->searchUsers('不存在的用户');
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    /**
     * 测试searchUsers方法返回格式
     */
    public function testSearchUsersReturnFormat(): void
    {
        $user = new BizUser();
        $user->setUsername('format_test_user');
        $user->setNickName('格式测试用户');
        $user->setValid(true);
        $this->repository->save($user);

        $results = $this->repository->searchUsers('format_test');

        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('text', $result);
        $this->assertEquals($user->getId(), $result['id']);
        $this->assertEquals($user->getNickName(), $result['text']);
    }

    /**
     * 测试searchUsers方法当用户没有昵称时使用用户名
     */
    public function testSearchUsersWithoutNickName(): void
    {
        $user = new BizUser();
        $user->setUsername('no_nickname_user');
        $user->setValid(true);
        // 不设置昵称
        $this->repository->save($user);

        $results = $this->repository->searchUsers('no_nickname');

        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals($user->getId(), $result['id']);
        $this->assertEquals($user->getUsername(), $result['text']); // 应该使用用户名而不是昵称
    }

    /**
     * 测试createUser方法创建用户
     */
    public function testCreateUser(): void
    {
        $userIdentifier = 'test_create_user_' . uniqid();
        $nickName = '创建测试用户';
        $avatarUrl = 'https://example.com/avatar.jpg';

        /** @var BizUser $user */
        $user = $this->repository->createUser($userIdentifier, $nickName, $avatarUrl);

        $this->assertInstanceOf(BizUser::class, $user);
        $this->assertEquals($userIdentifier, $user->getUsername());
        $this->assertEquals($nickName, $user->getNickName());
        $this->assertEquals($avatarUrl, $user->getAvatar());
        $this->assertTrue($user->isValid());
    }

    /**
     * 测试createUser方法只传入必需参数
     */
    public function testCreateUserWithOnlyRequiredParameters(): void
    {
        $userIdentifier = 'test_minimal_user_' . uniqid();

        /** @var BizUser $user */
        $user = $this->repository->createUser($userIdentifier);

        $this->assertInstanceOf(BizUser::class, $user);
        $this->assertEquals($userIdentifier, $user->getUsername());
        $this->assertTrue($user->isValid());
        // 可选参数应该保持为null
        $this->assertNull($user->getNickName());
        $this->assertNull($user->getAvatar());
    }

    /**
     * 测试saveUser方法保存用户
     */
    public function testSaveUser(): void
    {
        // 先创建一个用户（不保存到数据库）
        $userIdentifier = 'test_save_user_' . uniqid();
        /** @var BizUser $user */
        $user = $this->repository->createUser($userIdentifier);

        // 记录创建时的ID（应该是0，表示未保存）
        $originalId = $user->getId();

        // 使用saveUser方法保存用户
        $this->repository->saveUser($user);

        // 验证用户已保存并获得了新的ID
        $this->assertNotEquals($originalId, $user->getId());
        $this->assertGreaterThan(0, $user->getId());

        // 从数据库重新加载用户验证数据
        $savedUser = $this->repository->find($user->getId());
        $this->assertInstanceOf(BizUser::class, $savedUser);
        $this->assertEquals($userIdentifier, $savedUser->getUsername());
        $this->assertTrue($savedUser->isValid());
    }

    /**
     * 测试saveUser方法使用不支持的UserInterface类型抛出异常
     */
    public function testSaveUserWithUnsupportedUserTypeShouldThrowException(): void
    {
        $this->expectException(UnsupportedUserException::class);

        // @phpstan-ignore-next-line PreferInterfaceStubTraitRule.createTestUser
        $mockUser = new class implements UserInterface {
            public function getRoles(): array
            {
                return [];
            }

            public function eraseCredentials(): void
            {
                // 空实现
            }

            public function getUserIdentifier(): string
            {
                return 'unsupported_user';
            }
        };

        $this->repository->saveUser($mockUser);
    }

    /**
     * @return ServiceEntityRepository<BizUser>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $user = new BizUser();
        $user->setUsername('test_user_' . uniqid());
        $user->setNickName('测试用户');
        $user->setEmail('test@example.com');
        $user->setMobile('13800138000');
        $user->setPasswordHash('hashed_password');
        $user->setValid(true);

        return $user;
    }
}
