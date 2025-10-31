# biz-user-bundle

[![PHP 版本](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Symfony 版本](https://img.shields.io/badge/symfony-%5E6.4-green)](https://symfony.com)
[![许可证](https://img.shields.io/badge/license-MIT-brightgreen)](LICENSE)
[![构建状态](https://img.shields.io/badge/build-passing-brightgreen)](tests)
[![代码覆盖率](https://img.shields.io/badge/coverage-100%25-brightgreen)](tests)
[![测试](https://img.shields.io/badge/tests-passing-brightgreen)](tests)

[English](README.md) | [中文](README.zh-CN.md)

Symfony 应用的业务用户管理包。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [基础使用](#基础使用)
- [高级用法](#高级用法)
- [事件](#事件)
- [安全性](#安全性)
- [测试](#测试)
- [许可证](#许可证)

## 功能特性

- **用户管理**：完整的用户实体，支持身份验证
- **密码管理**：密码历史记录跟踪和强度验证
- **角色管理**：与 BizRole 系统集成的用户权限管理
- **用户迁移**：高级用户数据迁移和合并功能
- **属性系统**：与 user-attribute-bundle 集成的灵活用户数据
- **管理界面**：集成 EasyAdmin 的用户管理界面
- **事件系统**：用户身份查找事件
- **安全特性**：密码强度验证、历史记录跟踪

## 安装

```bash
composer require tourze/biz-user-bundle
```

## 配置

### 1. 注册 Bundle

```php
// config/bundles.php
return [
    // ...
    BizUserBundle\BizUserBundle::class => ['all' => true],
];
```

### 2. 配置服务

Bundle 会自动注册其服务。您可以在应用中覆盖它们：

```yaml
# config/services.yaml
services:
    # 覆盖用户服务
    BizUserBundle\Service\UserService:
        arguments:
            $passwordHistoryLimit: 5  # 要检查的历史密码数量
```

## 快速开始

安装后，按照以下步骤快速开始：

### 1. 配置 Bundle

```php
// config/bundles.php
return [
    // ...
    BizUserBundle\BizUserBundle::class => ['all' => true],
];
```

### 2. 创建您的第一个用户

```php
use BizUserBundle\Entity\BizUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// 在您的控制器或服务中
$user = new BizUser();
$user->setUsername('admin@example.com');
$user->setEmail('admin@example.com');
$user->setNickName('管理员');
$user->setValid(true);

// 哈希密码
$hashedPassword = $passwordHasher->hashPassword($user, '安全密码123!');
$user->setPasswordHash($hashedPassword);

$entityManager->persist($user);
$entityManager->flush();
```

### 3. 查找和验证用户

```php
use BizUserBundle\Service\UserService;

// 通过用户名或邮箱查找用户
$user = $userService->findUserByIdentity('admin@example.com');

// 检查用户是否为管理员
if ($userService->isAdmin($user)) {
    // 授予管理员权限
}
```

### 4. 验证密码强度

```php
try {
    $userService->checkNewPasswordStrength($user, '新密码123!');
    echo "密码足够强！";
} catch (PasswordWeakStrengthException $e) {
    echo "密码太弱：" . $e->getMessage();
}
```

## 基础使用

### 用户实体

`BizUser` 实体提供完整的用户实现：

```php
use BizUserBundle\Entity\BizUser;

$user = new BizUser();
$user->setUsername('john.doe@example.com');
$user->setNickName('张三');
$user->setEmail('john.doe@example.com');
$user->setPlainPassword('安全密码123!');
```

### 用户服务

`UserService` 提供各种用户操作：

```php
use BizUserBundle\Service\UserService;

// 通过身份标识查找用户
$user = $userService->findUserByIdentity('john.doe@example.com');

// 检查密码强度
$userService->checkNewPasswordStrength($user, '新密码123!');

// 检查用户是否为管理员
$isAdmin = $userService->isAdmin($user);
```

### 密码历史

跟踪密码历史以防止重复使用：

```php
use BizUserBundle\Entity\PasswordHistory;

$history = new PasswordHistory();
$history->setUser($user);
$history->setPasswordHash($hashedPassword);
```

## 高级用法

### 管理控制器

Bundle 提供开箱即用的 EasyAdmin 控制器：

- `BizUserCrudController` - 用户管理，提供完整的增删改查操作
- `PasswordHistoryCrudController` - 密码历史查看和审计

### 实体功能

`BizUser` 实体包含全面的用户数据字段：

```php
$user = new BizUser();
$user->setUsername('user@example.com');     // 必需的唯一用户名
$user->setIdentity('unique_id');            // 可选的外部标识符
$user->setNickName('显示名称');              // 用户友好的显示名称
$user->setEmail('user@example.com');        // 邮箱地址
$user->setMobile('13800138000');           // 手机号码（中国格式）
$user->setAvatar('avatar_url');            // 头像图片URL
$user->setType('admin');                   // 用户类型/类别
$user->setBirthday(new \DateTimeImmutable('1990-01-01'));
$user->setGender('male');
$user->setProvinceName('北京市');
$user->setCityName('北京市');
$user->setAreaName('朝阳区');
$user->setAddress('详细地址');
$user->setRemark('备注信息');
$user->setValid(true);                     // 启用/禁用用户
```

### 自定义用户身份解析

实现自定义用户身份解析逻辑：

```php
use BizUserBundle\Event\FindUserByIdentityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomUserIdentitySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FindUserByIdentityEvent::class => 'onFindUserByIdentity',
        ];
    }
    
    public function onFindUserByIdentity(FindUserByIdentityEvent $event): void
    {
        $identity = $event->getIdentity();
        
        // 自定义逻辑：通过外部ID查找
        if (preg_match('/^ext_(\d+)$/', $identity, $matches)) {
            $externalId = $matches[1];
            $user = $this->findUserByExternalId($externalId);
            if ($user) {
                $event->setUser($user);
            }
        }
    }
}
```

### 密码策略自定义

配置密码强度要求：

```php
// 在您的服务配置中
services:
    BizUserBundle\Service\UserService:
        arguments:
            $passwordHistoryLimit: 10  # 检查最近10个密码
            $passwordMinLength: 12     # 要求12位以上字符
```

### 用户数据迁移

在合并账户时迁移用户数据：

```php
use BizUserBundle\Service\UserService;

// 将所有数据从 sourceUser 迁移到 targetUser
$userService->migrate($sourceUser, $targetUser);

// 这将：
// - 找到所有引用 sourceUser 的实体
// - 将它们更新为引用 targetUser
// - 在数据库事务中处理迁移
```

### 用户创建和管理

```php
use BizUserBundle\Service\UserService;

// 创建新用户
$user = $userService->createUser('user@example.com', '显示名称', 'avatar_url');

// 保存用户
$userService->saveUser($user);

// 通过身份标识查找多个用户
$users = $userService->findUsersByIdentity('共享身份标识');
```

## 事件

### FindUserByIdentityEvent

在通过身份标识查找用户时触发：

```php
use BizUserBundle\Event\FindUserByIdentityEvent;

// 监听事件
class UserIdentitySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FindUserByIdentityEvent::class => 'onFindUserByIdentity',
        ];
    }
    
    public function onFindUserByIdentity(FindUserByIdentityEvent $event)
    {
        $identity = $event->getIdentity();
        // 自定义查找用户逻辑
        $user = $this->customFindUser($identity);
        if ($user) {
            $event->setUser($user);
        }
    }
}
```

### FindUsersByIdentityEvent

在通过多个身份标识查找用户时触发：

```php
use BizUserBundle\Event\FindUsersByIdentityEvent;

class BulkUserIdentitySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FindUsersByIdentityEvent::class => 'onFindUsersByIdentities',
        ];
    }
    
    public function onFindUsersByIdentities(FindUsersByIdentityEvent $event): void
    {
        $identities = $event->getIdentities();
        $users = $this->findUsersByCustomLogic($identities);
        $event->setUsers($users);
    }
}
```

## 安全性

### 密码要求

密码强度验证器要求密码至少包含以下 4 种中的 3 种：
- 大写字母
- 小写字母
- 数字
- 特殊字符

最小长度：8 个字符

### 密码安全

- **历史记录跟踪**：通过跟踪密码历史防止密码重复使用
- **强度验证**：强制执行强密码要求
- **安全哈希**：使用 Symfony 的密码哈希器进行安全密码存储

### 用户安全

- **有效标志**：用户可以被禁用而不被删除
- **基于角色的访问**：与基于角色的安全系统集成
- **审计跟踪**：跟踪用户创建和修改时间

### 最佳实践

1. **定期密码更新**：鼓励用户定期更新密码
2. **账户监控**：监控可疑的登录活动
3. **数据保护**：确保个人数据根据隐私法规进行处理
4. **访问控制**：实施适当的基于角色的访问控制

### 安全注意事项

- 在处理前始终验证用户输入
- 对所有用户身份验证流程使用 HTTPS
- 对登录尝试实施速率限制
- 定期审计用户账户和权限
- 保持 bundle 及其依赖项更新

## 测试

运行测试：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/biz-user-bundle/tests

# 运行并生成覆盖率报告
./vendor/bin/phpunit packages/biz-user-bundle/tests --coverage-html coverage

# 运行特定测试类
./vendor/bin/phpunit packages/biz-user-bundle/tests/Controller/Admin/BizUserCrudControllerTest.php
./vendor/bin/phpunit packages/biz-user-bundle/tests/Service/UserServiceTest.php
```

## 许可证

此 Bundle 在 MIT 许可证下发布。详情请参见 [LICENSE](LICENSE) 文件。