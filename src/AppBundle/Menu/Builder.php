<?php
// src/AppBundle/Menu/Builder.php
namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $acl = $this->container->get('acl_competition');
        $menu = $factory->createItem('root');

        ($acl->isGranted('DASHBOARD')) ? $menu->addChild('nav.dashboard', array('route' => 'dashboard', 'icon' => 'dashboard')) : '';
        ($acl->isGranted('STARTERS_VIEW')) ? $menu->addChild('nav.starters', array('route' => 'starters', 'icon' => 'group')) : '';
        // create another menu item
        ($acl->isGranted('SETTINGS_VIEW')) ? $menu->addChild('nav.competition', array('route' => 'competition', 'icon' => 'gears')) : '';
        ($acl->isGranted('PERMISSIONS_VIEW')) ? $menu->addChild('nav.permissions', array('route' => 'permissions', 'icon'=> 'lock')) : '';
        $menu->addChild('nav.profile', array('route' => 'fos_user_profile_edit', 'icon' => 'user-settings'));
        $menu->addchild('nav.log_out', array('route' => 'fos_user_security_logout', 'icon' => 'onoff'));
        // you can also add sub level's to your menu's as follows
        //$menu['About Me']->addChild('Edit profile', array('route' => ''));

        // ... add more children

        return $menu;
    }
}