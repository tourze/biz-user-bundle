<?php

namespace BizUserBundle\Controller\Admin;

use BizUserBundle\Entity\BizUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends AbstractCrudController<BizUser>
 */
class BizUserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
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
            ->setPageTitle(Crud::PAGE_DETAIL, '用户详情');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield ImageField::new('avatar', '头像')
            ->setBasePath('/uploads/avatars')
            ->setUploadDir('public/uploads/avatars')
            ->hideOnIndex();
        
        yield TextField::new('nickName', '昵称')
            ->setRequired(true)
            ->setColumns(6);
            
        yield TextField::new('username', '用户名')
            ->setRequired(true)
            ->setColumns(6);
            
        yield EmailField::new('email', '邮箱')
            ->setColumns(6);
            
        yield TextField::new('mobile', '手机号')
            ->setColumns(6);
            
        yield TextField::new('type', '用户类型')
            ->hideOnIndex()
            ->setColumns(6);
            
        yield TextField::new('identity', '唯一标识')
            ->hideOnIndex()
            ->setColumns(6);

        // 使用 plainPassword 字段处理密码
        if ($pageName === Crud::PAGE_NEW) {
            // 新建用户时密码为必填
            yield TextField::new('plainPassword', '密码')
                ->setFormType(PasswordType::class)
                ->setRequired(true)
                ->setColumns(6);
        } elseif ($pageName === Crud::PAGE_EDIT) {
            // 编辑用户时密码为可选
            yield TextField::new('plainPassword', '密码')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->setHelp('留空表示不修改密码')
                ->setColumns(6);
        }

        yield AssociationField::new('assignRoles', '分配角色')
            ->setRequired(false)
            ->autocomplete();
            
        yield BooleanField::new('valid', '有效状态')
            ->setHelp('是否启用此用户')
            ->renderAsSwitch(false);
            
        yield DateField::new('birthday', '生日')
            ->hideOnIndex();
            
        yield TextField::new('gender', '性别')
            ->hideOnIndex()
            ->setColumns(4);
            
        yield TextField::new('provinceName', '省份')
            ->hideOnIndex()
            ->setColumns(4);
            
        yield TextField::new('cityName', '城市')
            ->hideOnIndex()
            ->setColumns(4);
            
        yield TextField::new('areaName', '区域')
            ->hideOnIndex()
            ->setColumns(4);
            
        yield TextField::new('address', '详细地址')
            ->hideOnIndex()
            ->setColumns(8);
            
        yield TextareaField::new('remark', '备注')
            ->hideOnIndex()
            ->setNumOfRows(3);

        if ($pageName === Crud::PAGE_DETAIL) {
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('username', '用户名'))
            ->add(TextFilter::new('nickName', '昵称'))
            ->add(TextFilter::new('email', '邮箱'))
            ->add(TextFilter::new('mobile', '手机号'))
            ->add(TextFilter::new('type', '用户类型'))
            ->add(EntityFilter::new('assignRoles', '角色'))
            ->add(BooleanFilter::new('valid', '有效状态'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('新建用户');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel('删除');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('详情');
            });
    }

    /**
     * 处理用户创建和更新前的密码加密
     */
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizUser);
        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
        
        // 添加成功提示
        $this->addFlash('success', '用户创建成功！');
    }

    /**
     * 处理用户更新时的密码加密
     */
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizUser);
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
        
        // 添加成功提示
        $this->addFlash('success', '用户信息已更新！' . ($entityInstance->getPlainPassword() !== null ? '密码已修改。' : ''));
    }

    /**
     * 密码加密方法
     */
    private function encodePassword(BizUser $user): void
    {
        // 检查是否有明文密码需要加密
        if ($user->getPlainPassword() !== null && $user->getPlainPassword() !== '') {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPasswordHash($hashedPassword);
            $user->eraseCredentials(); // 清除明文密码
        }
    }

    /**
     * 处理用户删除
     */
    public function deleteEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizUser);
        $username = $entityInstance->getUsername();
        parent::deleteEntity($entityManager, $entityInstance);
        
        // 添加删除成功提示
        $this->addFlash('success', sprintf('用户"%s"已被删除！', (string) $username));
    }
}
