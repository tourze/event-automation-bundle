<?php

declare(strict_types=1);

namespace EventAutomationBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EventAutomationBundle\Entity\ContextConfig;

#[AdminCrud(
    routePath: '/event-automation/context-config',
    routeName: 'event_automation_context_config'
)]
final class ContextConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContextConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('上下文配置')
            ->setEntityLabelInPlural('事件上下文配置管理')
            ->setPageTitle(Crud::PAGE_INDEX, '上下文配置列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建上下文配置')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑上下文配置')
            ->setPageTitle(Crud::PAGE_DETAIL, '上下文配置详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['name', 'entityClass'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('eventConfig', '事件配置')
            ->setHelp('关联的事件自动化配置')
        ;

        yield TextField::new('name', '上下文变量名')
            ->setHelp('在事件处理中使用的变量名称')
        ;

        yield TextField::new('entityClass', '实体类名')
            ->setHelp('完整的实体类名，例如：App\Entity\User')
        ;

        yield TextareaField::new('querySql', '查询SQL')
            ->setHelp('获取上下文数据的SQL查询语句')
            ->setNumOfRows(5)
            ->hideOnIndex()
        ;

        yield ArrayField::new('queryParams', '查询参数配置')
            ->setHelp('查询参数的配置信息')
            ->hideOnIndex()
        ;

        yield BooleanField::new('valid', '有效状态')
            ->renderAsSwitch(false)
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('eventConfig')
            ->add(BooleanFilter::new('valid'))
            ->add('entityClass')
        ;
    }
}
