<?php

namespace Uteg\BaseBundle\Controller;

use Uteg\BaseBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends DefaultController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
        $comp = $comp = $this->getDoctrine()->getEntityManager()->find('UtegBaseBundle:Competition', $request->getSession()->get('comp'));

        return $this->render('UtegBaseBundle::dashboard.html.twig', array(
            "path" => array($comp->getName(), "dashboard.path")
        ));
    }
}