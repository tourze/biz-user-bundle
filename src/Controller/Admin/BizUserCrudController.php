<?php

namespace BizUserBundle\Controller\Admin;

use BizUserBundle\Entity\BizUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
            ->setEntityLabelInPlural('用户列表')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('fullName', '姓名');
        yield TextField::new('username', '用户名');
        yield EmailField::new('email', '邮箱');

        // 使用 plainPassword 字段处理密码
        if ($pageName === Crud::PAGE_NEW) {
            // 新建用户时密码为必填
            yield TextField::new('plainPassword', '密码')
                ->setFormType(PasswordType::class)
                ->setRequired(true);
        } elseif ($pageName === Crud::PAGE_EDIT) {
            // 编辑用户时密码为可选
            yield TextField::new('plainPassword', '密码')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->setHelp('留空表示不修改密码');
        }

        yield ChoiceField::new('roles', '角色')
            ->setChoices([
                '管理员' => 'ROLE_ADMIN',
                '普通用户' => 'ROLE_USER'
            ])
            ->allowMultipleChoices()
            ->renderExpanded();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel('删除');
            });
    }

    /**
     * 处理用户创建和更新前的密码加密
     */
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
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
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
        
        // 添加成功提示
        $this->addFlash('success', '用户信息已更新！' . ($entityInstance->getPlainPassword() ? '密码已修改。' : ''));
    }

    /**
     * 密码加密方法
     */
    private function encodePassword(BizUser $user): void
    {
        // 检查是否有明文密码需要加密
        if ($user->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            $user->eraseCredentials(); // 清除明文密码
        }
    }

    /**
     * 处理用户删除
     */
    public function deleteEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $username = $entityInstance->getUsername();
        parent::deleteEntity($entityManager, $entityInstance);
        
        // 添加删除成功提示
        $this->addFlash('success', sprintf('用户"%s"已被删除！', $username));
    }
}
