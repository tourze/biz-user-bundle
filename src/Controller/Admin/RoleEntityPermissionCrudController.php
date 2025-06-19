<?php

namespace BizUserBundle\Controller\Admin;

use BizUserBundle\Entity\RoleEntityPermission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<RoleEntityPermission>
 */
class RoleEntityPermissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RoleEntityPermission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('数据权限')
            ->setEntityLabelInPlural('数据权限管理')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['entityClass', 'statement', 'role.name', 'role.title'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '数据权限管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建数据权限')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑数据权限')
            ->setPageTitle(Crud::PAGE_DETAIL, '数据权限详情');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('role', '角色')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, RoleEntityPermission $entity) {
                return $entity->getRole() ? 
                    sprintf('%s (%s)', $entity->getRole()->getTitle(), $entity->getRole()->getName()) : 
                    '未分配';
            });
            
        yield TextField::new('entityClass', '实体类名')
            ->setRequired(true)
            ->setHelp('完整的实体类名，如：App\\Entity\\User')
            ->setColumns(12);
            
        yield TextareaField::new('statement', 'WHERE条件')
            ->setRequired(true)
            ->setHelp('SQL WHERE条件语句，不包含WHERE关键字')
            ->setNumOfRows(4);
            
        yield BooleanField::new('valid', '有效状态')
            ->setHelp('是否启用此数据权限')
            ->renderAsSwitch(false);
            
        yield TextareaField::new('remark', '备注')
            ->hideOnIndex()
            ->setHelp('对此数据权限的描述说明')
            ->setNumOfRows(3);

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('createdBy', '创建人')->hideOnForm();
            yield TextField::new('updatedBy', '更新人')->hideOnForm();
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('role', '角色'))
            ->add(TextFilter::new('entityClass', '实体类名'))
            ->add(BooleanFilter::new('valid', '有效状态'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('新建权限');
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
        assert($entityInstance instanceof RoleEntityPermission);
        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('数据权限"%s"创建成功！', $entityInstance->getEntityClass()));
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof RoleEntityPermission);
        parent::updateEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('数据权限"%s"更新成功！', $entityInstance->getEntityClass()));
    }

    public function deleteEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof RoleEntityPermission);
        $entityClass = $entityInstance->getEntityClass();
        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('数据权限"%s"删除成功！', $entityClass));
    }
}