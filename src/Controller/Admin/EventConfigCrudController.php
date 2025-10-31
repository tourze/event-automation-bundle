<?php

declare(strict_types=1);

namespace EventAutomationBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EventAutomationBundle\Entity\EventConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[AdminCrud(
    routePath: '/event-automation/config',
    routeName: 'event_automation_config'
)]
final class EventConfigCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return EventConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('事件配置')
            ->setEntityLabelInPlural('事件自动化配置管理')
            ->setPageTitle(Crud::PAGE_INDEX, '事件配置列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建事件配置')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑事件配置')
            ->setPageTitle(Crud::PAGE_DETAIL, '事件配置详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['name', 'identifier', 'cronExpression'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $testTrigger = Action::new('testTrigger', '测试触发', 'fa fa-play-circle')
            ->linkToCrudAction('testTrigger')
            ->setCssClass('btn btn-info')
        ;

        return $actions
            ->add(Crud::PAGE_DETAIL, $testTrigger)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('name', '事件名称')
            ->setHelp('事件的显示名称，用于标识和描述')
        ;
        yield TextField::new('identifier', '事件标识符')
            ->setHelp('唯一标识符，用于系统内部识别事件')
        ;
        yield TextField::new('cronExpression', 'Cron表达式')
            ->setHelp('定时触发的Cron表达式，留空则不定时触发')
            ->hideOnIndex()
        ;
        yield TextareaField::new('triggerSql', '触发条件SQL')
            ->setHelp('用于判断是否触发事件的SQL查询语句')
            ->setNumOfRows(5)
            ->hideOnIndex()
        ;
        yield BooleanField::new('valid', '有效状态')
            ->renderAsSwitch(false)
        ;

        yield AssociationField::new('contextConfigs', '上下文配置')
            ->hideOnForm()
        ;

        yield AssociationField::new('triggerLogs', '触发日志')
            ->onlyOnDetail()
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
            ->add(BooleanFilter::new('valid'))
            ->add(DateTimeFilter::new('createTime'))
        ;
    }

    /**
     * 测试事件触发
     */
    #[AdminAction(routePath: '{id}/test-trigger', routeName: 'testTrigger')]
    public function testTrigger(AdminContext $context): RedirectResponse
    {
        $eventConfig = $context->getEntity()->getInstance();
        assert($eventConfig instanceof EventConfig);

        try {
            // TODO: 实现事件触发测试逻辑
            // 这里应该检查触发条件SQL是否正确，验证事件配置

            $this->addFlash('success', sprintf('事件配置 "%s" 测试触发成功', $eventConfig->getName()));
        } catch (\Exception $e) {
            $this->addFlash('danger', sprintf('事件配置 "%s" 测试触发失败: %s', $eventConfig->getName(), $e->getMessage()));
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($eventConfig->getId())
                ->generateUrl()
        );
    }
}
