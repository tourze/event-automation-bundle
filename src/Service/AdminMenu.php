<?php

declare(strict_types=1);

namespace EventAutomationBundle\Service;

use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\TriggerLog;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 创建或获取事件自动化菜单组
        if (null === $item->getChild('事件自动化')) {
            $item->addChild('事件自动化')
                ->setAttribute('icon', 'fas fa-cogs')
            ;
        }

        $automationMenu = $item->getChild('事件自动化');
        if (null === $automationMenu) {
            return;
        }

        // 添加事件配置菜单
        $automationMenu->addChild('事件配置')
            ->setUri($this->linkGenerator->getCurdListPage(EventConfig::class))
            ->setAttribute('icon', 'fas fa-calendar-alt')
        ;

        // 添加上下文配置菜单
        $automationMenu->addChild('上下文配置')
            ->setUri($this->linkGenerator->getCurdListPage(ContextConfig::class))
            ->setAttribute('icon', 'fas fa-code-branch')
        ;

        // 添加触发日志菜单
        $automationMenu->addChild('触发日志')
            ->setUri($this->linkGenerator->getCurdListPage(TriggerLog::class))
            ->setAttribute('icon', 'fas fa-list-ul')
        ;
    }
}
