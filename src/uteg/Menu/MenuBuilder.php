<?php

namespace uteg\Menu;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\LoggingTranslator;
use uteg\ACL\ACLCompetition;
use uteg\EventListener\MenuEvent;


class MenuBuilder extends ContainerAware
{
    private $factory;
    private $em;
    private $translator;

    public function __construct(EntityManager $em, FactoryInterface $factory, Translator $translator)
    {
        $this->em = $em;
        $this->factory = $factory;
        $this->translator = $translator;
    }

    public function mainMenu(Request $request, ACLCompetition $acl, EventDispatcherInterface $eventDispatcher)
    {
        $menu = $this->factory->createItem('root');

        ($acl->isGranted('DASHBOARD')) ? $menu->addChild('nav.dashboard', array('route' => 'dashboard', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'dashboard', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        if ($acl->isGranted('STARTERS_VIEW')) {
            $menu->addChild('nav.starters', array('uri' => '#', 'icon' => 'user', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
            $menu['nav.starters']->addChild('nav.starters.male', array('route' => 'starters', 'routeParameters' => array('compid' => $request->get('compid'), 'gender' => 'male'), 'icon' => 'mars'));
            $menu['nav.starters']->addChild('nav.starters.female', array('route' => 'starters', 'routeParameters' => array('compid' => $request->get('compid'), 'gender' => 'female'), 'icon' => 'venus'));
            ($acl->isGranted('STARTERS_EDIT')) ? $menu['nav.starters']->addChild('nav.starters.import', array('route' => 'starterImport', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'upload')) : '';
        }

        ($acl->isGranted('CLUBS_VIEW')) ? $menu->addChild('nav.clubs', array('route' => 'clubs', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'group', 'labelAttributes' => array('class' => 'xn-text'))) : '';

        if ($acl->isGranted('CLUBS_VIEW')) {
            $menu->addChild('nav.invites', array('uri' => '#', 'icon' => 'envelope-o', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
            ($acl->isGranted('CLUBS_EDIT')) ? $menu['nav.invites']->addChild('nav.invites.invite', array('route' => 'invite', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'send')) : '';
            $menu['nav.invites']->addChild('nav.invites.list', array('route' => 'inviteList', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'bars'));
        }

        $eventDispatcher->dispatch(MenuEvent::SERVICE_MENU, new MenuEvent($this->factory, $menu, $request, $this->em, $this->translator));

        $menu->addChild('nav.reporting', array('uri' => '#', 'icon' => 'book', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));

        $eventDispatcher->dispatch(MenuEvent::REPORTING_MENU, new MenuEvent($this->factory, $menu, $request, $this->em, $this->translator));

        ($acl->isGranted('SETTINGS_VIEW')) ? $menu->addChild('nav.competition', array('route' => 'competition', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'cogs', 'labelAttributes' => array('class' => 'xn-text'))) : '';
        ($acl->isGranted('PERMISSIONS_VIEW')) ? $menu->addChild('nav.permissions', array('route' => 'permissions', 'routeParameters' => array('compid' => $request->get('compid')), 'icon' => 'lock', 'labelAttributes' => array('class' => 'xn-text'))) : '';

        return $menu;
    }

    public function breadcrumb(Request $request)
    {
        $comp = $this->em->find('uteg:Competition', $request->getSession()->get('comp'));

        $menu = $this->factory->createItem('root', array(
            'childrenAttributes' => array(
                'class' => 'breadcrumb',
            )
        ));
        // this item will always be displayed
        $menu->addChild($comp->getName() . " " . $comp->getStartdate()->format("Y"));

        // create the menu according to the route
        switch ($request->get('_route')) {
            case 'dashboard':
                $menu
                    ->addChild('dashboard.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'starters':
                $uri = $request->getRequestUri();

                $menu->addChild('starters.path');
                $menu->addChild((strpos($uri, 'female') == false) ? 'starters.path.male' : 'starters.path.female')
                    ->setCurrent(true);
                break;
            case 'starterImport':
                $menu->addChild('starters.path');
                $menu
                    ->addChild('starters.import.path')
                    ->setCurrent(true);
                break;
            case 'starter':
                $uri = explode("/", $request->getRequestUri());
                $uri = str_replace("%20", " ", end($uri));

                $menu->addChild('starters.path');
                $menu->addChild($uri)
                    ->setCurrent(true);
                break;
                break;
            case 'clubs':
                $menu
                    ->addChild('clubs.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'clubsInvite':
                $menu->addChild('clubs.path');
                $menu
                    ->addChild('clubs.invite.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'judges':
                $menu
                    ->addChild('judges.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'department':
                $menu->addChild('grouping.path');
                $menu
                    ->addChild('departments.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'division':
                $menu->addChild('grouping.path');
                $menu
                    ->addChild('divisions.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'reportingDivisions':
                $menu
                    ->addChild('reporting.path');
                $menu
                    ->addChild('egt.divisions.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'competition':
                $menu
                    ->addChild('competition.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
            case 'permissions':
                $menu
                    ->addChild('permissions.path')
                    ->setCurrent(true)// setCurrent is use to add a "current" css class
                ;
                break;
        }

        return $menu;
    }
}