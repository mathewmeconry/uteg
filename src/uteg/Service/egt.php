<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 10/10/15
 * Time: 12:45 AM
 */

namespace uteg\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use uteg\Entity\Competition;
use uteg\EventListener\MenuEvent;

class egt
{
    private $container;
    private $eventDispatcher;

    public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function init()
    {
        $this->eventDispatcher->addListener('uteg.addServiceMenu', array($this, 'onAddServiceMenu'));
    }

    public function onAddServiceMenu(MenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild('egt.nav.grouping', array('route' => 'grouping', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => 'group', 'labelAttributes' => array('class' => 'xn-text')));
    }

    public function grouping(Request $request, Competition $competition)
    {
        return $this->render('egt/grouping.html.twig', array(
            "comp" => $competition
        ));
    }
}