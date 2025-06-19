<?php

namespace BizUserBundle\Controller\Admin;

use BizUserBundle\Entity\PasswordHistory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<PasswordHistory>
 */
class PasswordHistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PasswordHistory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('密码历史')
            ->setEntityLabelInPlural('密码历史记录')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['username', 'userId'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '密码历史记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '密码历史详情');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id');
        
        yield TextField::new('username', '用户名')
            ->setColumns(6);
            
        yield TextField::new('userId', '用户ID')
            ->setColumns(6);
            
        yield TextField::new('ciphertext', '密码密文')
            ->hideOnIndex()
            ->formatValue(function ($value) {
                return $value ? '***已加密***' : '无';
            });
            
        yield BooleanField::new('needReset', '需要重置')
            ->renderAsSwitch(false);
            
        yield DateTimeField::new('expireTime', '过期时间')
            ->hideOnIndex();
            
        yield TextField::new('createdFromIp', '创建IP')
            ->hideOnIndex();
            
        yield DateTimeField::new('createTime', '创建时间');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('username', '用户名'))
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(BooleanFilter::new('needReset', '需要重置'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('expireTime', '过期时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('详情');
            });
    }
}