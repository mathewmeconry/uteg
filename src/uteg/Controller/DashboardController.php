<?php

namespace uteg\Controller;

use uteg\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends DefaultController
{
    /**
     * @Route("/{compid}/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request, $compid)
    {
        $comp = $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $request->getSession()->get('comp'));

        return $this->render('dashboard.html.twig', array(
            "path" => array($comp->getName(), "dashboard.path")
        ));
    }
}