<?php

namespace BizUserBundle\Service;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\PasswordHistory;
use BizUserBundle\Event\FindUserByIdentityEvent;
use BizUserBundle\Event\FindUsersByIdentityEvent;
use BizUserBundle\Exception\PasswordWeakStrengthException;
use BizUserBundle\Repository\BizUserRepository;
use BizUserBundle\Repository\PasswordHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\User\UserInterface;

#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'biz_user')]
readonly class UserService
{
    public function __construct(
        private BizUserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher,
        #[Autowire(service: 'biz-user.property-accessor')] private PropertyAccessor $propertyAccessor,
        private PasswordHasherFactoryInterface $hasherFactory,
        private PasswordHistoryRepository $historyRepository,
        private LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 根据唯一标志，查找用户信息
     * 理论上，这里是可能查找出多个的，但是不管那么多了吧。。
     */
    public function findUserByIdentity(string $identity): ?UserInterface
    {
        $user = $this->userRepository->findOneBy(['valid' => true, 'username' => $identity]);
        if (null !== $user) {
            return $user;
        }

        $user = $this->userRepository->findOneBy(['valid' => true, 'identity' => $identity]);
        if (null !== $user) {
            return $user;
        }

        // dispatch个事件出去，让其他bundle也能拦截这里的行为
        $event = new FindUserByIdentityEvent();
        $event->setIdentity($identity);
        $this->eventDispatcher->dispatch($event);

        return $event->getUser();
    }

    /**
     * 查找所有上关联的用户
     *
     * @return array<string, BizUser>
     */
    public function findUsersByIdentity(string $identity): array
    {
        $result = new ArrayCollection();

        $this->addUsersByIdentitySearch($result, $identity);
        $this->addUsersByNicknameIfEnabled($result, $identity);

        $event = $this->dispatchFindUsersEvent($identity, $result);

        return $this->extractUniqueBizUsers($event->getUsers());
    }

    /**
     * @param ArrayCollection<int, BizUser> $result
     */
    private function addUsersByIdentitySearch(ArrayCollection $result, string $identity): void
    {
        $userByUsername = $this->userRepository->findOneBy(['valid' => true, 'username' => $identity]);
        if (null !== $userByUsername) {
            $result->add($userByUsername);
        }

        $userByIdentity = $this->userRepository->findOneBy(['valid' => true, 'identity' => $identity]);
        if (null !== $userByIdentity) {
            $result->add($userByIdentity);
        }
    }

    /**
     * @param ArrayCollection<int, BizUser> $result
     */
    private function addUsersByNicknameIfEnabled(ArrayCollection $result, string $identity): void
    {
        if (!$this->isNicknameFindingEnabled()) {
            return;
        }

        $users = $this->userRepository->findBy(['valid' => true, 'nickName' => $identity]);
        foreach ($users as $nickNameUser) {
            $result->add($nickNameUser);
        }
    }

    private function isNicknameFindingEnabled(): bool
    {
        return isset($_ENV['FIND_USER_BY_NICKNAME']) && 'true' === $_ENV['FIND_USER_BY_NICKNAME'];
    }

    /**
     * @param ArrayCollection<int, BizUser> $users
     */
    private function dispatchFindUsersEvent(string $identity, ArrayCollection $users): FindUsersByIdentityEvent
    {
        $event = new FindUsersByIdentityEvent();
        $event->setIdentity($identity);
        // @phpstan-ignore-next-line BizUser implements UserInterface so this is safe
        $event->setUsers($users);
        $this->eventDispatcher->dispatch($event);

        return $event;
    }

    /**
     * @param Collection<int, UserInterface> $users
     * @return array<string, BizUser>
     */
    private function extractUniqueBizUsers(Collection $users): array
    {
        $uniqueUsers = [];
        foreach ($users->toArray() as $item) {
            if ($item instanceof BizUser) {
                $uniqueUsers[$item->getUserIdentifier()] = $item;
            }
        }

        return $uniqueUsers;
    }

    /**
     * 在一些特殊情景中，我们需要将两个用户的数据合并
     */
    public function migrate(BizUser $sourceUser, BizUser $targetUser): void
    {
        $this->entityManager->beginTransaction();

        try {
            $this->migrateUserReferences($sourceUser, $targetUser, $this->entityManager);
            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            $this->logger->error('合并用户时发生异常', [
                'exception' => $exception,
                'sourceUser' => $sourceUser,
                'targetUser' => $targetUser,
            ]);
            $this->entityManager->rollback();
        }
    }

    /**
     * 迁移用户引用关系
     */
    private function migrateUserReferences(BizUser $sourceUser, BizUser $targetUser, EntityManagerInterface $em): void
    {
        $metas = $em->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $this->processEntityMetadata($meta, $sourceUser, $targetUser, $em);
        }
    }

    /**
     * 处理实体元数据
     */
    private function processEntityMetadata(mixed $meta, BizUser $sourceUser, BizUser $targetUser, EntityManagerInterface $em): void
    {
        if (!is_object($meta) || !method_exists($meta, 'getName') || !method_exists($meta, 'getReflectionClass')) {
            return;
        }

        $entityClass = $meta->getName();
        if (BizUser::class === $entityClass) {
            return; // 不支持本身这个类
        }

        $reflection = $meta->getReflectionClass();
        $userProperties = $this->findUserProperties($reflection);

        foreach ($userProperties as $property) {
            $this->updateEntityReferences($entityClass, $property, $sourceUser, $targetUser, $em);
        }
    }

    /**
     * 查找指向用户的属性
     *
     * @param \ReflectionClass<object> $reflection
     *
     * @return \ReflectionProperty[]
     */
    private function findUserProperties(\ReflectionClass $reflection): array
    {
        $userProperties = [];
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $property) {
            if ($this->isUserProperty($property)) {
                $userProperties[] = $property;
            }
        }

        return $userProperties;
    }

    /**
     * 检查属性是否指向用户类型
     */
    private function isUserProperty(\ReflectionProperty $property): bool
    {
        $type = $property->getType();

        return $type instanceof \ReflectionNamedType && BizUser::class === $type->getName();
    }

    /**
     * 更新实体引用
     */
    private function updateEntityReferences(string $entityClass, \ReflectionProperty $property, BizUser $sourceUser, BizUser $targetUser, EntityManagerInterface $em): void
    {
        if (!class_exists($entityClass)) {
            return;
        }

        $repo = $em->getRepository($entityClass);
        $rows = $repo->findBy([$property->getName() => $sourceUser]);

        foreach ($rows as $row) {
            $this->propertyAccessor->setValue($row, $property->getName(), $targetUser);
            $em->persist($row);
            $em->flush();
            $em->detach($row);
            unset($row);
        }
    }

    /**
     * 检查密码是否够健壮
     */
    public function checkNewPasswordStrength(BizUser $user, #[\SensitiveParameter] string $plainPassword): void
    {
        $this->validatePasswordLength($plainPassword);
        $this->validatePasswordComplexity($plainPassword);
        $this->validatePasswordHistory($user, $plainPassword);
    }

    /**
     * 验证密码长度
     */
    private function validatePasswordLength(string $plainPassword): void
    {
        if (strlen($plainPassword) < 8) {
            throw new PasswordWeakStrengthException('密码长度至少 8 位');
        }
    }

    /**
     * 验证密码复杂度
     */
    private function validatePasswordComplexity(string $plainPassword): void
    {
        $ruleCount = $this->countPasswordRules($plainPassword);
        if ($ruleCount < 3) {
            throw new PasswordWeakStrengthException('密码至少包含大写字母、小写字母、阿拉伯数字、特殊字符中的 3 种');
        }
    }

    /**
     * 统计密码规则符合数量
     */
    private function countPasswordRules(string $plainPassword): int
    {
        $rules = [
            '/[A-Z]/',      // 大写字母
            '/[a-z]/',      // 小写字母
            '/\d/',         // 数字
            '/[^A-Za-z0-9]/', // 特殊字符
        ];

        $ruleCount = 0;
        foreach ($rules as $rule) {
            if (1 === preg_match($rule, $plainPassword)) {
                ++$ruleCount;
            }
        }

        return $ruleCount;
    }

    /**
     * 验证密码历史
     */
    private function validatePasswordHistory(BizUser $user, string $plainPassword): void
    {
        $histories = $this->getPasswordHistories($user);
        $hasher = $this->hasherFactory->getPasswordHasher($user);

        foreach ($histories as $history) {
            if ($this->isPasswordMatch($history, $hasher, $plainPassword)) {
                throw new PasswordWeakStrengthException('新密码不能与前 5 次使用过的密码相同');
            }
        }
    }

    /**
     * 获取密码历史记录
     * @return PasswordHistory[]
     */
    private function getPasswordHistories(BizUser $user): array
    {
        /** @var PasswordHistory[] */
        return $this->historyRepository->createQueryBuilder('a')
            ->where('a.userId = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 检查密码是否匹配历史记录
     */
    private function isPasswordMatch(PasswordHistory $history, mixed $hasher, string $plainPassword): bool
    {
        if (null === $history->getCiphertext() || '' === $history->getCiphertext()) {
            return false;
        }

        if (!is_object($hasher) || !method_exists($hasher, 'verify')) {
            return false;
        }

        return $hasher->verify($history->getCiphertext(), $plainPassword);
    }

    public function isAdmin(BizUser $user): bool
    {
        foreach ($user->getAssignRoles() as $role) {
            if (true === $role->isValid() && true === $role->isAdmin()) {
                return true;
            }
        }

        return false;
    }

    public function findOrCreateUserByMobile(string $mobile): UserInterface
    {
        $user = $this->userRepository->findOneBy(['mobile' => $mobile, 'valid' => true]);

        if (null !== $user) {
            return $user;
        }

        return $this->userRepository->createUser($mobile, null, null);
    }
}
