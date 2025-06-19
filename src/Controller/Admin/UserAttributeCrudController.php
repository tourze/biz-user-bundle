<?php

namespace BizUserBundle\Controller\Admin;

use BizUserBundle\Entity\UserAttribute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<UserAttribute>
 */
class UserAttributeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserAttribute::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户属性')
            ->setEntityLabelInPlural('用户属性管理')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'value', 'user.username', 'user.nickName'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '用户属性管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建用户属性')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑用户属性')
            ->setPageTitle(Crud::PAGE_DETAIL, '用户属性详情');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('user', '用户')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, UserAttribute $entity) {
                return $entity->getUser() ? 
                    sprintf('%s (%s)', $entity->getUser()->getNickName(), $entity->getUser()->getUsername()) : 
                    '未分配';
            });
            
        yield TextField::new('name', '属性名')
            ->setRequired(true)
            ->setHelp('属性的名称标识符')
            ->setColumns(6);
            
        yield TextareaField::new('value', '属性值')
            ->setRequired(true)
            ->setHelp('属性的值内容')
            ->setNumOfRows(3);
            
        yield TextareaField::new('remark', '备注')
            ->hideOnIndex()
            ->setHelp('对此属性的描述说明')
            ->setNumOfRows(2);

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('createdBy', '创建人')->hideOnForm();
            yield TextField::new('updatedBy', '更新人')->hideOnForm();
            yield TextField::new('createdFromIp', '创建IP')->hideOnForm();
            yield TextField::new('updatedFromIp', '更新IP')->hideOnForm();
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('user', '用户'))
            ->add(TextFilter::new('name', '属性名'))
            ->add(TextFilter::new('value', '属性值'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('新建属性');
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

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof UserAttribute);
        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('用户属性"%s"创建成功！', $entityInstance->getName()));
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof UserAttribute);
        parent::updateEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('用户属性"%s"更新成功！', $entityInstance->getName()));
    }

    public function deleteEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof UserAttribute);
        $name = $entityInstance->getName();
        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('用户属性"%s"删除成功！', $name));
    }
}