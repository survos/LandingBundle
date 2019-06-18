<?php

namespace Survos\LandingBundle\Menu;

use Knp\Menu\FactoryInterface;

class LandingMenuBuilder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->addChild('Home', ['route' => 'survos_landing'])
            ->setAttribute('icon', 'fa fa-home');
        $menu->addChild('xx', ['route' => 'survos_landing']);
        $menu->addChild('Three', ['route' => 'survos_landing']);
        // ... add more children

        return $menu;
    }

    public function createTestMenu(array $options)
    {

        $menu = $this->factory->createItem('root');
        $menu->addChild(
            'linking',
            [
                'route' => 'profile',
            ]
        );

        $menu->addChild(
            'texting',
            [
                'labelAttributes' => [
                    'class' => 'class3 class4',
                ],
            ]
        );

        $dropdown = $menu->addChild(
            'Hello Me',
            [
                'attributes' => [
                    'dropdown' => true,
                ],
            ]
        );

        $dropdown->addChild(
            'Profile',
            [
                'route' => 'profile',
                'attributes' => [
                    'divider_append' => true,
                ],
            ]
        );

        $dropdown->addChild(
            'text',
            [
                'attributes' => [
                    'icon' => 'fa fa-user-circle',
                ],
                'labelAttributes' => [
                   // 'class' => ['class1', 'class2'],
                ],
            ]
        );

        $dropdown->addChild(
            'Logout',
            [
                'route' => 'logout',
                'attributes' => [
                    'divider_prepend' => true,
                    'icon' => 'fa fa-sign-out',
                ],
            ]
        );
        /*
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->addChild('Projects', array('uri' => '#acme_hello_projects'))
            ->setAttribute('icon', 'fa fa-list');
        $menu->addChild('Employees', array('uri' => '#acme_hello_employees'))
            ->setAttribute('icon', 'fa fa-group');
        */
        return $menu;
    }
}