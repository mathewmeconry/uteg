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
	 * @Route("/starters/{sex}", name="starterspost", defaults={"sex": "male"}, requirements={"sex": "male|female"})
	 * @Method("POST")
	 */
	public function startersPostAction($sex, Request $request) {
		$this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');
		setlocale(LC_TIME, $request->getLocale());
		$dateFormatter = $this->get('bcc_extra_tools.date_formatter');
		
		
		if($sex !== 'male' && $sex !== 'female') {
			return $this->redirect($request->getRequestUri()."/male", 301);
		} else {
			
			$comp = $this->getDoctrine()->getEntityManager()->find('AppBundle:Competition', $request->getSession()->get('comp'));
			$s2cs = $comp->getS2cs()->toArray();
			$starters = array();

			foreach($s2cs as $s2c) {
				$starters["data"][] = array("firstname" => $s2c->getStarter()->getFirstname(),
					"lastname" => $s2c->getStarter()->getLastname(),
					"birthdate" => $dateFormatter->format($s2c->getStarter()->getBirthdate(), 'medium', 'none', $request->getLocale()),
					"club" => $s2c->getClub()->getName(),
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