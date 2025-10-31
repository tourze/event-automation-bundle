<?php

declare(strict_types=1);

namespace EventAutomationBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EventAutomationBundle\Entity\TriggerLog;

#[AdminCrud(
    routePath: '/event-automation/trigger-log',
    routeName: 'event_automation_trigger_log'
)]
final class TriggerLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TriggerLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('触发日志')
            ->setEntityLabelInPlural('事件触发日志管理')
            ->setPageTitle(Crud::PAGE_INDEX, '触发日志列表')
            ->setPageTitle(Crud::PAGE_DETAIL, '触发日志详情')
            ->setDefaultSort(['createTime' => 'DESC'])
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

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield CodeEditorField::new('contextData', '上下文数据')
                ->setLanguage('javascript')
                ->setHelp('触发时的上下文数据，JSON格式')
                ->setNumOfRows(10)
            ;
        } else {
            yield TextareaField::new('contextData', '上下文数据')
                ->setHelp('触发时的上下文数据，JSON格式')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $value;
                })
            ;
        }

        yield TextareaField::new('result', '执行结果')
            ->setHelp('事件执行的结果信息')
            ->setNumOfRows(5)
            ->hideOnIndex()
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
            ->add(DateTimeFilter::new('createTime'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
        ;
    }
}
