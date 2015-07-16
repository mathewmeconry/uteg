<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="uteg")
     */
    public function rootAction() {
        return $this->forward('uteg:Redirecting:checkAuthentication', array("target" => "/competitions"));
    }
    
    /**
     * @Route("/flashbag", name="parseFlashbag")
     */
    public function flashbagAction() {
    	return $this->render('parseFlashbag.html.twig');
    }

    /**
     * @Route("/autocomplete/starters", name="autocompleteStarters")
     */
    public function autocompleteStartersAction() {
        $em = $this->getDoctrine()->getManager();
        $starters = $em->getRepository('uteg:Starter')->findAll();

        foreach($starters as $starter) {
            $result[] = array('id' => $starter->getId(),
                "firstname" => $starter->getFirstname(),
                "lastname" => $starter->getLastname(),
                "birthyear" => $starter->getBirthyear());
        }

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}