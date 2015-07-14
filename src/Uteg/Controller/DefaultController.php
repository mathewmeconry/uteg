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
}