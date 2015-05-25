<?php 

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class Breadcrumb 
{
	private $factory;
	
	public function __construct(FactoryInterface $factory) {
		$this->factory = $factory;
	}
	
	public function breadcrumb(Request $request) {
		$menu = $this->factory->createItem('root');
		// this item will always be displayed
		$menu->addChild('UTEG');
		$menu->addChild($request->getSession()->get('comp')->getName());
		 
		// create the menu according to the route
		switch($request->get('_route')){
			case 'dashboard':
				$menu
				->addChild('dashboard.path')
				->setCurrent(true)
				// setCurrent is use to add a "current" css class
				;
				break;
			case 'starters':
                $uri = $request->getRequestUri();

                $menu->addChild('starters.path');
                $menu->addChild((strpos($uri, 'female') == false) ? 'starters.path.male' : 'starters.path.female')
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
			case 'competition':
                $menu
                    ->addChild('competition.path')
                    ->setCurrent(true)
                    // setCurrent is use to add a "current" css class
                ;
                break;
			case 'permissions':
                $menu
                    ->addChild('permissions.path')
                    ->setCurrent(true)
                    // setCurrent is use to add a "current" css class
                ;
                break;
		}
		 
		return $menu;
	}
}