<?php
/**
 * Created by PhpStorm.
 * User: Mathias Scherer
 * Date: 02.01.2016
 * Time: 20:23
 */

namespace uteg\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;


class MenuEvent extends Event
{
    const SERVICE_MENU = 'uteg.addServiceMenu';

    private $factory;
    private $menu;

    public function __construct(FactoryInterface $factory, ItemInterface $menu)
    {
        $this->factory = $factory;
        $this->menu = $menu;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getMenu()
    {
        return $this->menu;
    }
}