<?php 

namespace AppBundle\Controller;

use AppBundle\Form\Type\StarterType;
use AppBundle\Form\Type\S2cType;
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
                "sextrans" => $sextrans,
                "comp" => $this->getDoctrine()->getEntityManager()->find('AppBundle:Competition', $request->getSession()->get('comp'))
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
				$starters["data"][] = array("id" => $s2c->getId(),
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

    /**
     * @Route("/starter/edit/{id}", name="starterEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function starterEditAction($id, Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $s2c = $this->getDoctrine()->getEntityManager()->find('AppBundle:Starters2Competitions',$id);

        $form = $this->createForm(new S2cType(), $s2c);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $s2c = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();

            $em->persist($s2c);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'competitionlist.addcomp.success');

            return new Response('true');
        }

        return $this->render('form/StarterEdit.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/starter/remove", name="starterRemove")
     */
    public function starterRemoveAction(Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $em = $this->getDoctrine()->getEntityManager();
        $s2c = $em->find('AppBundle:Starters2Competitions', $_POST['id']);
        $competition = $em->find('AppBundle:Competition', $request->getSession()->get('comp'));

        $competition->removeS2c($s2c);
        $em->persist($competition);
        $em->flush();
        return new Response('true');
    }
	
	private function getName($id) {
		return "Mathias Scherer";
	}
}