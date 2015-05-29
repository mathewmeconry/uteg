<?php 

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StartersController extends Controller 
{
	/**
	 * @Route("/starters/{sex}", name="starters", defaults={"sex": "male"}, requirements={"sex": "male|female"})
	 * @Method("GET")
	 */
	public function startersGetAction($sex, Request $request)
	{
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');

		$requestUri = explode("/", $request->getRequestUri());
		 
		if(end($requestUri) !== 'male' && end($requestUri) !== 'female') {
			return $this->redirect($request->getRequestUri()."/male", 301);
		} else {
            $sexshort = (end($requestUri) == 'female') ? 'f' : 'm';
            $sextrans = ($sex == 'female') ? 'starters.female' : 'starters.male';

			return $this->render('starters.html.twig', array(
                "sex" => $sexshort,
                "sextrans" => $sextrans
			));
		}
	}
	
	/**
	 * @Route("/starters/{sex}/{cat}", name="starterspost", defaults={"sex": "male", "cat": "0"}, requirements={"sex": "male|female", "cat": "\d+"})
	 * @Method("POST")
	 */
	public function startersPostAction($sex, $cat, Request $request) {
		$this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');
		setlocale(LC_TIME, $request->getLocale());
		$dateFormatter = $this->get('bcc_extra_tools.date_formatter');
		
		
		if($sex !== 'male' && $sex !== 'female') {
			return $this->redirect($request->getRequestUri()."/male", 301);
		} else {
			$comp = $this->getDoctrine()->getEntityManager()->find('AppBundle:Competition', $request->getSession()->get('comp'));
			$s2cs = ($cat == 0) ? $comp->getS2csBySex(substr($sex,0,1)) : $comp->getS2csBySexCat(substr($sex,0,1), $cat);
			$starters = array();

			foreach($s2cs as $s2c) {
				$starters["data"][] = array("id" => $s2c->getStarter()->getId(),
					"firstname" => $s2c->getStarter()->getFirstname(),
					"lastname" => $s2c->getStarter()->getLastname(),
					"birthyear" => $s2c->getStarter()->getBirthyear(),
					"club" => $s2c->getClub()->getName(),
					"category" => ($s2c->getCategory()->getNumber() == 8) ? ($s2c->getStarter()->getSex() == 'f') ? $s2c->getCategory()->getName()."D" : $s2c->getCategory()->getName()."H" : $s2c->getCategory()->getName(),
					"present" => $s2c->getPresent(),
					"medicalcert" => $s2c->getMedicalcert()
				);
			}
		
			$response = new Response(json_encode($starters));
			$response->headers->set('Content-Type', 'application/json');
			
			return $response;
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