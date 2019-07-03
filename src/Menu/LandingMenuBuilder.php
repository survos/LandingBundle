<?php

namespace Survos\LandingBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LandingMenuBuilder
{
    private $factory;
    private $authorizationChecker;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav mr-auto');
        $menu->addChild('Home', ['route' => 'survos_landing'])
            ->setAttribute('icon', 'fa fa-home');
        $menu->addChild('xx', ['route' => 'survos_landing']);
        $menu->addChild('Three', ['route' => 'survos_landing']);
        // ... add more children

        return $this->cleanupMenu($menu);
    }

    public function createAuthMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        // hack?  Seems like this should be in the renderer.  Top Level ul tag
        $menu->setChildrenAttribute('class', 'navbar-nav mr-auto');
        $dropdown = $menu->addChild(
            'my_account',
            [
                'attributes' => [
                    'dropdown' => true,
                ],
            ]
        );

        if ($this->authorizationChecker->isGranted('ROLE_USER')) {


        $dropdown->addChild(
            'Profile',
            [
                'route' => 'profile',
                'attributes' => [
                    'icon' => 'fa fa-user-circle',
                    'divider_append' => true,
                ],
            ]
        );

        $dropdown->addChild(
            'Logout',
            [
                'route' => 'logout',
                'attributes' => [
                    'divider_prepend' => true,
                    'icon' => 'fas fa-sign-out-alt',
                ],
            ]
        );
    } else {
            $dropdown->addChild(
                'Login',
                [
                    'route' => 'login',
                    'attributes' => [
                        'divider_prepend' => true,
                        'icon' => 'fas fa-sign-in-alt',
                    ],
                ]
            );

            $dropdown->addChild(
                'Register',
                [
                    'route' => 'register',
                    'attributes' => [
                        'icon' => 'fas fa-user',
                    ],
                ]
            );

        }

        return $this->cleanupMenu($menu);

    }

    public function createTestMenu(array $options)
    {

        $menu = $this->factory->createItem('root');
        // hack?  Seems like this should be in the renderer.  Top Level ul tag
        $menu->setChildrenAttribute('class', 'navbar-nav mr-auto');

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
                    'icon' => 'fas fa-sign-out-alt',
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

        return $this->cleanupMenu($menu);
        // return $menu;
    }

    private function cleanupMenu(ItemInterface $menu): ItemInterface
    {

        $menu->setChildrenAttribute('class', 'navbar-nav');
// menu items
        foreach ($menu as $child) {
            $child->setLinkAttribute('class', 'nav-link')
                ->setAttribute('class', 'nav-item');
        }
        return $menu;
    }
}
