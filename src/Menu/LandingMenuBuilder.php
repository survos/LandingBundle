<?php

namespace Survos\LandingBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class LandingMenuBuilder
{
    /* protected, so that subclasses can use it. */
    protected $factory;
    protected $authorizationChecker;
    private $security;
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $security, ClientRegistry $clientRegistry)
    {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
        $this->security = $security;
        $this->clientRegistry = $clientRegistry;
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav mr-auto');
        $menu->addChild('Home', ['route' => 'app_homepage'])
            ->setAttribute('icon', 'fa fa-home');
        // ... add more children

        return $this->cleanupMenu($menu);
    }

    // check for social media.  Arguably a separate bundle or at least file
    public function createSocialMenu(array $options = [])
    {
        $menu = $this->factory->createItem('root');
        // hack?  Seems like this should be in the renderer.  Top Level ul tag
        $menu->setChildrenAttribute('class', 'navbar-nav mr-auto');
        foreach ($this->clientRegistry->getEnabledClientKeys() as $clientKey) {
            $menu->addChild('connect_' . $clientKey, [
                'route' => 'oauth_connect_start',
                'routeParameters' => [
                    'clientKey' => $clientKey
                ]
            ]);
        }

        $menu = $this->cleanupMenu($menu);

        return $menu;

    }

    public function isGranted($attribute) {
        return $this->authorizationChecker->isGranted($attribute);
    }

    public function createAuthMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        // hack?  Seems like this should be in the renderer.  Top Level ul tag
        $menu->setChildrenAttribute('class', 'navbar-nav mr-auto');

        $loggedIn = false;
        if ($user = $this->security->getToken()->getUser()) {
            $loggedIn = is_object($user); // 'anon.' string
        }



        // if ($loggedIn)
        if ($this->authorizationChecker->isGranted('ROLE_USER'))
        {
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                try {
                    $menu->addChild(
                        'easyadmin',
                        [
                            'route' => 'easyadmin',
                            'attributes' => [
                                'icon' => 'fas fa-wrench',
                            ],
                        ]
                    );
                } catch (RouteNotFoundException $e) {
                    // the route is not defined...
                }
            }

            $dropdown = $menu->addChild(
                'my_account',
                [
                    'label' => $this->security->getToken()->getUsername(),
                    'attributes' => [
                        'dropdown' => true,
                    ],
                ]
            );

            if ($this->authorizationChecker->isGranted('ROLE_USER')) {
                try {
                    $dropdown->addChild(
                        'Profile',
                        [
                            'route' => 'app_profile',
                            'attributes' => [
                                'icon' => 'fa fa-user-circle',
                                // 'divider_append' => true,
                            ],
                        ]
                    );
                } catch (RouteNotFoundException $e) {
                    // the route is not defined...
                }

                $dropdown->addChild(
                    'Logout',
                    [
                        'route' => 'app_logout',
                        'attributes' => [
                            // 'divider_prepend' => true,
                            'icon' => 'fas fa-sign-out-alt',
                        ],
                    ]
                );

            }
        } else {
            $dropdown = $menu->addChild(
                'my_account',
                [
                    'label' => $this->security->getToken()->getUsername(),
                    'attributes' => [
                        'dropdown' => true,
                    ],
                ]
            );


            try {
                    $dropdown->addChild(
                        'Login',
                        [
                            'route' => 'app_login',
                            'attributes' => [
                                // 'divider_prepend' => true,
                                'icon' => 'fas fa-sign-in-alt',
                            ],
                        ]
                    );
                } catch (RouteNotFoundException $e) {
                    // the route is not defined..., bin/console make:registration-form

                }

                // go through all the social media options

                try {
                    $dropdown->addChild(
                        'github_login',
                        [
                            'route' => 'connect_github_start',
                            'attributes' => [
                                // 'divider_prepend' => true,
                                'icon' => 'fab fa-github',
                            ],
                        ]
                    );
                } catch (RouteNotFoundException $e) {
                    // the route is not defined..., bin/console make:registration-form

                }

                try {
                    $dropdown->addChild(
                        'google_login',
                        [
                            'route' => 'connect_google_start',
                            'attributes' => [
                                // 'divider_prepend' => true,
                                'icon' => 'fab fa-google',
                            ],
                        ]
                    );
                } catch (RouteNotFoundException $e) {
                    // the route is not defined..., bin/console make:registration-form

                }

                try {
                    $dropdown->addChild(
                        'Register',
                        [
                            'route' => 'app_register',
                            'attributes' => [
                                'icon' => 'fas fa-user',
                            ],
                        ]
                    );
                } catch (RouteNotFoundException $e) {
                    // the route is not defined..., bin/console make:registration-form

                }
            }
        try {
        } catch (RouteNotFoundException $e) {
                // routes likely not loaded.
        } catch (\Exception $e) {
            // we should really handle this.
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

    public function cleanupMenu(ItemInterface $menu): ItemInterface
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
