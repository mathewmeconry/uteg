<?php 

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StartersController extends Controller 
{
	/**
	 * @Route("/starters/{sex}", name="starters", defaults={"sex": "male"}, requirements={"sex": "male|female"})
	 */
	public function startersAction($sex, Request $request)
	{
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');

        $em = $this->getDoctrine()->getEntityManager();

		$requestUri = explode("/", $request->getRequestUri());
		 
		if(end($requestUri) !== 'male' && end($requestUri) !== 'female') {
			return $this->redirect($request->getRequestUri()."/male", 301);
		} else {
            $sex = (end($requestUri) == 'female') ? 'f' : 'm';
            $sextrans = ($sex == 'f') ? 'starters.female' : 'starters.male';
            $comp = $request->getSession()->get('comp');
            $starters = $comp->getUsers();
            var_dump($em->getRepository('\AppBundle\Entity\Starters2Competitions')->findOneBy(array("id" => 2)->getCompid()));
			return $this->render('starters.html.twig', array(
                "sex" => $sex,
                "sextrans" => $sextrans,
                "starters" => $starters
			));
		}
	}
	
	/**
	 * @Route("/starter/{id}/{name}", name="starter", defaults={"name": ""}, requirements={"id": "\d+"})
	 */
	public function starterAction($id, $name, Request $request)
	{
		if($name === "") {
			return $this->redirect($request->getRequestUri()."/".$this->getName($id), 301);
		} else {
			return $this->render('starter.html.twig', array(
                    "title" => $name,
					"path" => array($request->getSession()->get('comp')->getName(), 'starter.path', $name)
			));
		}
	}
	
	private function getName($id) {
		return "Mathias Scherer";
	}
}