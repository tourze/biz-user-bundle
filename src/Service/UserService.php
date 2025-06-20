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
use Doctrine\Common\Collections\Criteria;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\User\UserInterface;

#[Autoconfigure(public: true)]
class UserService
{
    public function __construct(
        private readonly BizUserRepository $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        #[Autowire(service: 'biz-user.property-accessor')] private readonly PropertyAccessor $propertyAccessor,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
        private readonly PasswordHistoryRepository $historyRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 根据唯一标志，查找用户信息
     * 理论上，这里是可能查找出多个的，但是不管那么多了吧。。
     */
    public function findUserByIdentity(string $identity): ?UserInterface
    {
        $user = $this->userRepository->findOneBy(['valid' => true, 'username' => $identity]);
        if ($user !== null) {
            return $user;
        }

        $user = $this->userRepository->findOneBy(['valid' => true, 'identity' => $identity]);
        if ($user !== null) {
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

        $user = $this->userRepository->findOneBy(['valid' => true, 'username' => $identity]);
        if ($user !== null) {
            $result->add($user);
        }

        $user = $this->userRepository->findOneBy(['valid' => true, 'identity' => $identity]);
        if ($user !== null) {
            $result->add($user);
        }

        if (isset($_ENV['FIND_USER_BY_NICKNAME']) && $_ENV['FIND_USER_BY_NICKNAME'] === 'true') {
            $users = $this->userRepository->findBy(['valid' => true, 'nickName' => $identity]);
            foreach ($users as $nickNameUser) {
                $result->add($nickNameUser);
            }
        }

        // dispatch个事件出去，让其他bundle也能拦截这里的行为
        $event = new FindUsersByIdentityEvent();
        $event->setIdentity($identity);
        $event->setUsers($result);
        $this->eventDispatcher->dispatch($event);

        // 进行一次去重
        $uniqueUsers = [];
        foreach ($event->getUsers()->toArray() as $item) {
            /* @var BizUser $item */
            $uniqueUsers[$item->getUserIdentifier()] = $item;
        }

        return $uniqueUsers;
    }

    /**
     * 在一些特殊情景中，我们需要将两个用户的数据合并
     */
    public function migrate(BizUser $sourceUser, BizUser $targetUser): void
    {
        $em = $this->userRepository->em();
        $em->beginTransaction();

        try {
            $metas = $em->getMetadataFactory()->getAllMetadata();
            foreach ($metas as $meta) {
                $entityClass = $meta->getName();
                if (BizUser::class === $entityClass) {
                    // 不支持本身这个类
                    continue;
                }

                // 遍历其中的成员，看是否是用户，是的话就查找并更新一次
                $reflection = $meta->getReflectionClass();
                foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE) as $property) {
                    $type = $property->getType();
                    if ($type === null) {
                        continue;
                    }
                    if (!$type instanceof \ReflectionNamedType || BizUser::class !== $type->getName()) {
                        continue;
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
            }
            $em->commit();
        } catch (\Throwable $exception) {
            $this->logger->error('合并用户时发生异常', [
                'exception' => $exception,
                'sourceUser' => $sourceUser,
                'targetUser' => $targetUser,
            ]);
            $em->rollback();
        }
    }

    /**
     * 检查密码是否够健壮
     */
    public function checkNewPasswordStrength(BizUser $user, #[\SensitiveParameter] string $plainPassword): void
    {
        if (strlen($plainPassword) < 8) {
            throw new PasswordWeakStrengthException('密码长度至少 8 位');
        }

        // 密码至少包含大写字母、小写字母、阿拉伯数字、特殊字符中的 3 种；
        // 定义包含大写字母、小写字母、数字和特殊字符的正则表达式
        $uppercaseRegex = '/[A-Z]/';
        $lowercaseRegex = '/[a-z]/';
        $digitRegex = '/\d/';
        $specialCharRegex = '/[^A-Za-z0-9]/';
        // 统计符合规则的数量
        $ruleCount = 0;
        if (preg_match($uppercaseRegex, $plainPassword)) {
            ++$ruleCount;
        }
        if (preg_match($lowercaseRegex, $plainPassword)) {
            ++$ruleCount;
        }
        if (preg_match($digitRegex, $plainPassword)) {
            ++$ruleCount;
        }
        if (preg_match($specialCharRegex, $plainPassword)) {
            ++$ruleCount;
        }
        if ($ruleCount < 3) {
            throw new PasswordWeakStrengthException('密码至少包含大写字母、小写字母、阿拉伯数字、特殊字符中的 3 种');
        }

        // 新密码不能与前 5 次使用过的密码相同
        $hasher = $this->hasherFactory->getPasswordHasher($user);
        $histories = $this->historyRepository->createQueryBuilder('a')
            ->where('a.userId = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('a.id', Criteria::DESC)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
        foreach ($histories as $history) {
            /** @var PasswordHistory $history */
            if (empty($history->getCiphertext())) {
                continue;
            }
            if ($hasher->verify($history->getCiphertext(), $plainPassword)) {
                throw new PasswordWeakStrengthException('新密码不能与前 5 次使用过的密码相同');
            }
        }
    }

    public function isAdmin(BizUser $user): bool
    {
        foreach ($user->getAssignRoles() as $role) {
            if ($role->isValid() && $role->isAdmin()) {
                return true;
            }
        }

        return false;
    }
}
