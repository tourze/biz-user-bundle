<?php

namespace BizUserBundle\Controller\Admin;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Repository\BizUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tourze\FileStorageBundle\Field\ImageGalleryField;

/**
 * @extends AbstractCrudController<BizUser>
 */
#[AdminCrud(
    routePath: '/biz-user/user',
    routeName: 'biz_user_user'
)]
final class BizUserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly BizUserRepository $bizUserRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return BizUser::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户')
            ->setEntityLabelInPlural('用户管理')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['username', 'nickName', 'email', 'mobile'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '用户管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建用户')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑用户')
            ->setPageTitle(Crud::PAGE_DETAIL, '用户详情')
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // 如果不是 ROLE_ADMIN，只显示当前用户的信息
        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if ($user instanceof BizUser) {
                $queryBuilder->andWhere('entity.id = :currentUserId')
                    ->setParameter('currentUserId', $user->getId())
                ;
            }
        }

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        yield from $this->getBasicFields();
        yield from $this->getPasswordField($pageName);
        yield from $this->getRoleFields();
        yield from $this->getAdditionalFields();
        yield from $this->getTimestampFields($pageName);
    }

    /**
     * 获取基础字段
     * @return iterable<int, FieldInterface>
     */
    private function getBasicFields(): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();

        // 头像字段：端到端验证 ImageGalleryField
        yield ImageGalleryField::new('avatar', '头像')
            ->setHelp('点击选择图片，从素材库中挑选头像')
            ->setColumns(6)
        ;

        yield TextField::new('nickName', '昵称')
            ->setRequired(true)
            ->setColumns(6)
        ;

        yield TextField::new('username', '用户名')
            ->setRequired(true)
            ->setHelp('用户名必须唯一，不能与其他用户重复')
            ->setColumns(6)
        ;

        yield EmailField::new('email', '邮箱')
            ->setHelp('邮箱地址必须唯一')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value ?? '-';
            })
        ;

        yield TextField::new('mobile', '手机号码')
            ->setHelp('手机号码必须唯一')
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value ?? '-';
            })
        ;

        yield TextField::new('identity', '唯一标识')
            ->setHelp('身份标识必须唯一，不能与其他用户重复')
            ->hideOnIndex()
            ->setColumns(6)
            ->formatValue(function ($value) {
                return $value ?? '-';
            })
        ;
    }

    /**
     * 获取密码字段
     * @return iterable<int, FieldInterface>
     */
    private function getPasswordField(string $pageName): iterable
    {
        if (Crud::PAGE_NEW === $pageName) {
            yield TextField::new('plainPassword', '密码')
                ->setFormType(PasswordType::class)
                ->setRequired(true)
                ->setColumns(6)
            ;
        } elseif (Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('plainPassword', '密码')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->setHelp('留空表示不修改密码')
                ->setColumns(6)
            ;
        }
    }

    /**
     * 获取角色字段
     * @return iterable<int, FieldInterface>
     */
    private function getRoleFields(): iterable
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            yield AssociationField::new('assignRoles', '分配角色')
                ->setRequired(false)
                ->autocomplete()
                ->formatValue(function ($value, BizUser $entity) {
                    return $this->formatRoleNames($entity);
                })
            ;
        } else {
            yield AssociationField::new('assignRoles', '已分配角色')
                ->onlyOnDetail()
                ->formatValue(function ($value, BizUser $entity) {
                    return $this->formatRoleNames($entity);
                })
            ;
        }
    }

    /**
     * 获取附加字段
     * @return iterable<int, FieldInterface>
     */
    private function getAdditionalFields(): iterable
    {
        yield BooleanField::new('valid', '是否启用此用户')
            ->setHelp('有效状态')
            ->renderAsSwitch(false)
        ;

        yield TextareaField::new('remark', '备注')
            ->hideOnIndex()
            ->setNumOfRows(3)
            ->formatValue(function ($value) {
                return $value ?? '-';
            })
        ;
    }

    /**
     * 获取时间戳字段
     * @return iterable<int, FieldInterface>
     */
    private function getTimestampFields(string $pageName): iterable
    {
        if (Crud::PAGE_DETAIL === $pageName) {
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    /**
     * 格式化角色名称
     */
    private function formatRoleNames(BizUser $entity): string
    {
        try {
            $roles = $entity->getAssignRoles();
            if ([] === $roles) {
                return '-';
            }

            $roleNames = [];
            foreach ($roles as $role) {
                $roleNames[] = $role->getTitle();
            }

            return implode(', ', $roleNames);
        } catch (\Exception $e) {
            return '-';
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('username', '用户名'))
            ->add(TextFilter::new('nickName', '昵称'))
            ->add(TextFilter::new('email', '邮箱'))
            ->add(EntityFilter::new('assignRoles', '角色'))
            ->add(BooleanFilter::new('valid', '有效状态'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('详情');
            })
        ;

        // 尝试更新EDIT动作（如果存在的话）
        try {
            $actions = $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('编辑');
            });
        } catch (\InvalidArgumentException) {
            // 如果EDIT动作不存在，先添加它再更新
            $actions = $actions
                ->add(Crud::PAGE_INDEX, Action::EDIT)
                ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                    return $action->setIcon('fa fa-edit')->setLabel('编辑');
                })
            ;
        }

        // 只有 ROLE_ADMIN 才能新增和删除用户
        if ($this->isGranted('ROLE_ADMIN')) {
            // 尝试更新DELETE动作（如果存在的话）
            try {
                $actions = $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                    return $action->setIcon('fa fa-trash')->setLabel('删除');
                });
            } catch (\InvalidArgumentException) {
                // 如果DELETE动作不存在，先添加它再更新
                $actions = $actions
                    ->set(Crud::PAGE_INDEX, Action::DELETE)
                    ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                        return $action->setIcon('fa fa-trash')->setLabel('删除');
                    })
                ;
            }
        } else {
            // 非管理员禁用新建和删除操作
            $actions = $actions
                ->disable(Action::NEW)
                ->disable(Action::DELETE)
            ;
        }

        return $actions;
    }

    /**
     * 校验用户唯一性
     *
     * @return bool 返回true表示校验通过，false表示有错误
     */
    private function validateUserUniqueness(BizUser $user, bool $isNew = true): bool
    {
        return $this->validateUsernameUniqueness($user, $isNew)
            && $this->validateEmailUniqueness($user, $isNew)
            && $this->validateMobileUniqueness($user, $isNew)
            && $this->validateIdentityUniqueness($user, $isNew);
    }

    /**
     * 校验用户名唯一性
     */
    private function validateUsernameUniqueness(BizUser $user, bool $isNew): bool
    {
        return $this->validateFieldUniqueness(
            $user,
            'username',
            $user->getUsername(),
            '用户名已存在，请使用其他用户名',
            $isNew
        );
    }

    /**
     * 校验邮箱唯一性
     */
    private function validateEmailUniqueness(BizUser $user, bool $isNew): bool
    {
        return $this->validateFieldUniqueness(
            $user,
            'email',
            $user->getEmail(),
            '邮箱地址已存在，请使用其他邮箱',
            $isNew
        );
    }

    /**
     * 校验手机号唯一性
     */
    private function validateMobileUniqueness(BizUser $user, bool $isNew): bool
    {
        return $this->validateFieldUniqueness(
            $user,
            'mobile',
            $user->getMobile(),
            '手机号码已存在，请使用其他手机号',
            $isNew
        );
    }

    /**
     * 校验身份标识唯一性
     */
    private function validateIdentityUniqueness(BizUser $user, bool $isNew): bool
    {
        return $this->validateFieldUniqueness(
            $user,
            'identity',
            $user->getIdentity(),
            '身份标识已存在，请使用其他标识',
            $isNew
        );
    }

    /**
     * 通用字段唯一性校验
     *
     * @param BizUser $user 要校验的用户
     * @param string $field 字段名
     * @param string|null $value 字段值
     * @param string $errorMessage 错误消息
     * @param bool $isNew 是否为新建用户
     * @return bool
     */
    private function validateFieldUniqueness(BizUser $user, string $field, ?string $value, string $errorMessage, bool $isNew): bool
    {
        if (null === $value || '' === $value) {
            return true;
        }

        $existingUser = $this->bizUserRepository->findOneBy([$field => $value]);

        if (null !== $existingUser && ($isNew || $existingUser->getId() !== $user->getId())) {
            $this->addFlash('danger', $errorMessage);

            return false;
        }

        return true;
    }

    /**
     * 处理用户创建和更新前的密码加密
     * @param mixed $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizUser);

        // 校验唯一性
        if (!$this->validateUserUniqueness($entityInstance, true)) {
            return;
        }

        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);

        // 添加成功提示
        $this->addFlash('success', '用户创建成功！');
    }

    /**
     * 处理用户更新时的密码加密
     * @param mixed $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizUser);

        // 校验唯一性
        if (!$this->validateUserUniqueness($entityInstance, false)) {
            return;
        }

        $hasPassword = null !== $entityInstance->getPlainPassword() && '' !== $entityInstance->getPlainPassword();
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);

        // 添加成功提示
        $this->addFlash('success', '用户信息已更新！' . ($hasPassword ? '密码已修改。' : ''));
    }

    /**
     * 密码加密方法
     */
    private function encodePassword(BizUser $user): void
    {
        // 检查是否有明文密码需要加密
        if (null !== $user->getPlainPassword() && '' !== $user->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPasswordHash($hashedPassword);
            $user->__serialize(); // 清除明文密码
        }
    }

    /**
     * 处理用户删除
     * @param mixed $entityInstance
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizUser);
        $username = $entityInstance->getUsername();
        parent::deleteEntity($entityManager, $entityInstance);

        // 添加删除成功提示
        $this->addFlash('success', sprintf('用户"%s"已被删除！', $username));
    }
}
