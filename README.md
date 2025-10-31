# biz-user-bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E6.4-green)](https://symfony.com)
[![License](https://img.shields.io/badge/license-MIT-brightgreen)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](tests)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](tests)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen)](tests)

[English](README.md) | [中文](README.zh-CN.md)

Business user management bundle for Symfony applications.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
- [Events](#events)
- [Security](#security)
- [Testing](#testing)
- [License](#license)

## Features

- **User Management**: Complete user entity with authentication support
- **Password Management**: Password history tracking and strength validation
- **Role Management**: Integration with BizRole system for user permissions
- **User Migration**: Advanced user data migration and merging capabilities
- **Attribute System**: Integration with user-attribute-bundle for flexible user data
- **Admin Interface**: EasyAdmin integration for user management
- **Event System**: Events for user identity lookups
- **Security Features**: Password strength validation, history tracking

## Installation

```bash
composer require tourze/biz-user-bundle
```

## Configuration

### 1. Register the Bundle

```php
// config/bundles.php
return [
    // ...
    BizUserBundle\BizUserBundle::class => ['all' => true],
];
```

### 2. Configure Services

The bundle automatically registers its services. You can override them in your application:

```yaml
# config/services.yaml
services:
    # Override user service
    BizUserBundle\Service\UserService:
        arguments:
            $passwordHistoryLimit: 5  # Number of previous passwords to check
```

## Quick Start

After installation, follow these steps to get started quickly:

### 1. Configure the Bundle

```php
// config/bundles.php
return [
    // ...
    BizUserBundle\BizUserBundle::class => ['all' => true],
];
```

### 2. Create Your First User

```php
use BizUserBundle\Entity\BizUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// In your controller or service
$user = new BizUser();
$user->setUsername('admin@example.com');
$user->setEmail('admin@example.com');
$user->setNickName('Administrator');
$user->setValid(true);

// Hash the password
$hashedPassword = $passwordHasher->hashPassword($user, 'SecurePass123!');
$user->setPasswordHash($hashedPassword);

$entityManager->persist($user);
$entityManager->flush();
```

### 3. Find and Authenticate Users

```php
use BizUserBundle\Service\UserService;

// Find a user by username or email
$user = $userService->findUserByIdentity('admin@example.com');

// Check if user is admin
if ($userService->isAdmin($user)) {
    // Grant admin access
}
```

### 4. Validate Password Strength

```php
try {
    $userService->checkNewPasswordStrength($user, 'newPassword123!');
    echo "Password is strong enough!";
} catch (PasswordWeakStrengthException $e) {
    echo "Password too weak: " . $e->getMessage();
}
```

## Basic Usage

### User Entity

The `BizUser` entity provides a complete user implementation:

```php
use BizUserBundle\Entity\BizUser;

$user = new BizUser();
$user->setUsername('john.doe@example.com');
$user->setNickName('John Doe');
$user->setEmail('john.doe@example.com');
$user->setPlainPassword('securePassword123!');
```

### User Service

The `UserService` provides various user operations:

```php
use BizUserBundle\Service\UserService;

// Find user by identity
$user = $userService->findUserByIdentity('john.doe@example.com');

// Check password strength
$userService->checkNewPasswordStrength($user, 'newPassword123!');

// Check if user is admin
$isAdmin = $userService->isAdmin($user);
```

### Password History

Track password history to prevent reuse:

```php
use BizUserBundle\Entity\PasswordHistory;

$history = new PasswordHistory();
$history->setUser($user);
$history->setPasswordHash($hashedPassword);
```

## Advanced Usage

### Admin Controllers

The bundle provides ready-to-use EasyAdmin controllers:

- `BizUserCrudController` - User management with full CRUD operations
- `PasswordHistoryCrudController` - Password history viewing and auditing

### Entity Features

The `BizUser` entity includes comprehensive user data fields:

```php
$user = new BizUser();
$user->setUsername('user@example.com');     // Required unique username
$user->setIdentity('unique_id');            // Optional external identifier  
$user->setNickName('Display Name');         // User-friendly display name
$user->setEmail('user@example.com');        // Email address
$user->setMobile('13800138000');           // Mobile phone (Chinese format)
$user->setAvatar('avatar_url');            // Profile picture URL
$user->setType('admin');                   // User type/category
$user->setBirthday(new \DateTimeImmutable('1990-01-01'));
$user->setGender('male');
$user->setProvinceName('北京市');
$user->setCityName('北京市');
$user->setAreaName('朝阳区');
$user->setAddress('详细地址');
$user->setRemark('备注信息');
$user->setValid(true);                     // Enable/disable user
```

### Custom User Identity Resolution

Implement custom user identity resolution logic:

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
        
        // Custom logic: find by external ID
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

### Password Policy Customization

Configure password strength requirements:

```php
// In your service configuration
services:
    BizUserBundle\Service\UserService:
        arguments:
            $passwordHistoryLimit: 10  # Check last 10 passwords
            $passwordMinLength: 12     # Require 12+ characters
```

### User Data Migration

Merge user data when consolidating accounts:

```php
use BizUserBundle\Service\UserService;

// Migrate all data from sourceUser to targetUser
$userService->migrate($sourceUser, $targetUser);

// This will:
// - Find all entities that reference sourceUser
// - Update them to reference targetUser instead
// - Handle the migration in a database transaction
```

### User Creation and Management

```php
use BizUserBundle\Service\UserService;

// Create a new user
$user = $userService->createUser('user@example.com', 'Display Name', 'avatar_url');

// Save the user
$userService->saveUser($user);

// Find multiple users by identity
$users = $userService->findUsersByIdentity('shared_identity');
```

## Events

### FindUserByIdentityEvent

Dispatched when finding a user by identity:

```php
use BizUserBundle\Event\FindUserByIdentityEvent;

// Listen to the event
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
        // Custom logic to find user
        $user = $this->customFindUser($identity);
        if ($user) {
            $event->setUser($user);
        }
    }
}
```

### FindUsersByIdentityEvent

Dispatched when finding multiple users by identities:

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

## Security

### Password Requirements

The password strength validator requires passwords to contain at least 3 of the following:
- Uppercase letters
- Lowercase letters
- Numbers
- Special characters

Minimum length: 8 characters

### Password Security

- **History Tracking**: Prevents password reuse by tracking password history
- **Strength Validation**: Enforces strong password requirements
- **Secure Hashing**: Uses Symfony's password hasher for secure password storage

### User Security

- **Valid Flag**: Users can be disabled without deletion
- **Role-based Access**: Integration with role-based security systems
- **Audit Trail**: Track user creation and modification times

### Best Practices

1. **Regular Password Updates**: Encourage users to update passwords regularly
2. **Account Monitoring**: Monitor for suspicious login activities
3. **Data Protection**: Ensure personal data is handled according to privacy regulations
4. **Access Controls**: Implement proper role-based access controls

### Security Considerations

- Always validate user input before processing
- Use HTTPS for all user authentication flows
- Implement rate limiting for login attempts
- Regularly audit user accounts and permissions
- Keep the bundle and its dependencies updated

## Testing

Run the tests:

```bash
# Run all tests
./vendor/bin/phpunit packages/biz-user-bundle/tests

# Run with coverage
./vendor/bin/phpunit packages/biz-user-bundle/tests --coverage-html coverage

# Run specific test classes
./vendor/bin/phpunit packages/biz-user-bundle/tests/Controller/Admin/BizUserCrudControllerTest.php
./vendor/bin/phpunit packages/biz-user-bundle/tests/Service/UserServiceTest.php
```

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.