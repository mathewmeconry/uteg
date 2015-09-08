<?php
// src/Uteg/BaseBundle/Menu/MainMenu.php
namespace Uteg\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MainMenu extends ContainerAware
{

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $acl = $this->container->get('acl_competition');
        $menu = $factory->createItem('root');

        ($acl->isGranted('DASHBOARD')) ? $menu->addChild('nav.dashboard', array('route' => 'dashboard', 'icon' => 'dashboard', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        if ($acl->isGranted('STARTERS_VIEW')) {
            $menu->addChild('nav.starters', array('uri' => '#', 'icon' => 'user', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
            $menu['nav.starters']->addChild('nav.starters.male', array('uri' => '/starters/male', 'icon' => 'male'));
            $menu['nav.starters']->addChild('nav.starters.female', array('uri' => '/starters/female', 'icon' => 'female'));
            ($acl->isGranted('STARTERS_EDIT')) ? $menu['nav.starters']->addChild('nav.starters.import', array('route' => 'starterImport', 'icon' => 'upload')) : '';

            $menu->addChild('nav.divisions', array('uri' => '#', 'icon' => 'sitemap', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
            $menu['nav.divisions']->addChild('nav.divisions.squads', array('route' => 'divSquads', 'icon' => 'users'));
            $menu['nav.divisions']->addChild('nav.divisions.settings', array('route' => 'divSettings', 'icon' => 'cogs'));
        }

        ($acl->isGranted('CLUBS_VIEW')) ? $menu->addChild('nav.clubs', array('route' => 'clubs', 'icon' => 'globe', 'labelAttributes' => array('class' => 'xn-text'))) : '';

        if ($acl->isGranted('CLUBS_VIEW')) {
            $menu->addChild('nav.invites', array('uri' => '#', 'icon' => 'envelope-o', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
            ($acl->isGranted('CLUBS_EDIT')) ? $menu['nav.invites']->addChild('nav.invites.invite', array('route' => 'invite', 'icon' => 'send')) : '';
            $menu['nav.invites']->addChild('nav.invites.list', array('route' => 'inviteList', 'icon' => 'bars'));
        }

        ($acl->isGranted('SETTINGS_VIEW')) ? $menu->addChild('nav.competition', array('route' => 'competition', 'icon' => 'cogs', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        ($acl->isGranted('PERMISSIONS_VIEW')) ? $menu->addChild('nav.permissions', array('route' => 'permissions', 'icon' => 'lock', 'labelAttributes' => array('class' => 'xn-text'))) : '';

        return $menu;
    }
}