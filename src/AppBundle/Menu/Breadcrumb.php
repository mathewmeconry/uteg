<?php 

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class Breadcrumb 
{
	private $factory;
	private $em;
	
	public function __construct(EntityManager $em, FactoryInterface $factory) {
		$this->em = $em;
		$this->factory = $factory;
	}
	
	public function breadcrumb(Request $request) {
		$comp = $this->em->find('AppBundle:Competition', $request->getSession()->get('comp'));
		
		$menu = $this->factory->createItem('root', array(
		    'childrenAttributes'    => array(
		        'class'             => 'breadcrumb',
		    )
        ));
		// this item will always be displayed
		$menu->addChild($comp->getName()." ".$comp->getStartdate()->format("Y"));
		 
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