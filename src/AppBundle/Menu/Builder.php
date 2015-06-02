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

        ($acl->isGranted('DASHBOARD')) ? $menu->addChild('nav.dashboard', array('route' => 'dashboard', 'icon' => 'dashboard', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        if($acl->isGranted('STARTERS_VIEW')) {
        	$menu->addChild('nav.starters', array('uri' => '#', 'icon' => 'group', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        	$menu['nav.starters']->addChild('nav.starters.male', array('uri' => '/starters/male', 'icon' => 'male'));
        	$menu['nav.starters']->addChild('nav.starters.female', array('uri' => '/starters/female', 'icon' => 'female'));
            ($acl->isGranted('STARTERS_EDIT') ? $menu['nav.starters']->addChild('nav.starters.import', array('route' => 'starterImport', 'icon' => 'upload')) : '');
        }

        // create another menu item
        ($acl->isGranted('SETTINGS_VIEW')) ? $menu->addChild('nav.competition', array('route' => 'competition', 'icon' => 'gears', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        ($acl->isGranted('PERMISSIONS_VIEW')) ? $menu->addChild('nav.permissions', array('route' => 'permissions', 'icon'=> 'lock', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        // you can also add sub level's to your menu's as follows
        //$menu['About Me']->addChild('Edit profile', array('route' => ''));

        // ... add more children

        return $menu;
    }
}