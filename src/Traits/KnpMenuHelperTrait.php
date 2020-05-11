<?php // Simplies the construction of menu items,
namespace Survos\LandingBundle\Traits;

use Knp\Menu\ItemInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function Symfony\Component\String\u;

trait KnpMenuHelperTrait
{
    private function addMenuItem(ItemInterface $menu, array $options): ItemInterface
    {
        $options = $this->menuOptions($options);
        // must pass in either route, icon or menu_code

        if (!$options['menu_code']) {
            $options['menu_code'] = $options['route'] ?: (u($options['label'] ?: $options['icon'])->snake() );
        }

        $child = $menu->addChild($options['menu_code'], $options);
        if ($icon = $options['icon']) {
            $child->setLabelAttribute('icon', $icon);
        }

        return $child;

    }
    private function menuOptions(array $options, array $extra = []): array
    {
        // idea: make the label a . version of the route, e.g. project_show could be project.show
        // we could also set a default icon for things like edit, show
        $options = (new OptionsResolver())
            ->setDefaults([
                'menu_code' => null,
                'route' => null,
                'rp' => null,
                '_fragment' => null,
                'label' => null,
                'icon' => null,
                'description' => null,
                'attributes' => []
            ])->resolve($options);

        // rename rp
        if (is_object($options['rp'])) {
            $options['routeParameters'] = $options['rp']->getRp();
            $iconConstant = get_class($options['rp']) . '::ICON';
            $options['icon'] = defined($iconConstant) ? constant($iconConstant) : 'fas fa-database'; // generic database entity
        } elseif (is_array($options['rp'])) {
            $options['routeParameters'] = $options['rp'];
        }
        // if (isset($options['rp'])) { dd($options);}
        unset($options['rp']);
        if (empty($options['label']) && $options['route']) {
            $options['label'] = u($options['route'])->replace('_', ' ')->title(true)->afterLast(' ')->toString();
        }

        if (empty($options['label']) && $options['menu_code']) {
            $options['label'] = u($options['menu_code'])->replace('.', ' ')->title(true)->toString();
        }

        // if label is exactly true then automate the label from the route
        if ($options['label'] === true) {
            $options['label'] = str_replace('_', '.', $options['route']);
        }

        // we could pass in a hash route and hash params instead.
        if ($fragment = $options['_fragment']) {
            $options['uri'] = '#' . $fragment;
            unset($options['route']);
            //
        }

        // default icons, should be configurable in survos_landing.yaml
        if ($options['icon'] === null) {
            foreach ([
                         'show' => 'fas fa-eye',
                         'edit' => 'fas fa-wrench'
                     ] as $regex=>$icon) {
                if (preg_match("|$regex|", $options['route'])) {
                    $options['icon'] = $icon;
                }
            }
        }

        // move the icon to attributes, where it belongs
        if ($options['icon']) {
            $options['attributes']['icon'] = $options['icon'];
            $options['label_attributes']['icon'] = $options['icon'];
            // unset($options['icon']);
        }
        return $options;
    }

    public function authMenu(AuthorizationCheckerInterface $security, ItemInterface $menu, $childOptions=[])
    {
        if ($security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $menu->addChild(
                'logout',
                ['route' => 'app_logout', 'label' => 'menu.logout', 'childOptions' => $childOptions]
            )->setLabelAttribute('icon', 'fas fa-sign-out-alt');
        } else {
            $menu->addChild(
                'login',
                ['route' => 'app_login', 'label' => 'menu.login', 'childOptions' => $childOptions]
            )->setLabelAttribute('icon', 'fas fa-sign-in-alt');
            $menu->addChild(
                'register',
                ['route' => 'app_register', 'label' => 'menu.register', 'childOptions' => $childOptions]
            )->setLabelAttribute('icon', 'fas fa-sign-in-alt');
        }

    }


}