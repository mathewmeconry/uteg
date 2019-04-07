<?php
/**
 * Created by PhpStorm.
 * User: Mathias Scherer
 * Date: 02.01.2016
 * Time: 20:23
 */

namespace uteg\EventListener;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\LoggingTranslator;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;


class MenuEvent extends Event
{
    const SERVICE_MENU = 'uteg.addServiceMenu';
    const REPORTING_MENU = 'uteg.addReportingMenu';

    private $factory;
    private $menu;
    private $request;
    private $em;
    private $translator;

    public function __construct(FactoryInterface $factory, ItemInterface $menu, Request $request, EntityManager $em, $translator)
    {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->request = $request;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function getRequest()
    {
        return $this->request;
    }
    
    public function getEntityManager()
    {
        return $this->em;
    }
    
    public function getTranslator()
    {
        return $this->translator;
    }
}