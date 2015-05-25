<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
       return $this->render('dashboard.html.twig', array(
        		"path" => array($request->getSession()->get('comp')->getName(), "dashboard.path")
        ));
    }

    /**
     * @Route("/createuser", name="usercreation")
     */
    public function usercreationAction() {
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setFirstname("Mathias");
        $user->setLastname("Scherer");
        $user->setEmail("scherer.mat@gmail.com");
        $user->setPassword("123");

        $userManager->updateUser($user);

        return $this->render('created');
    }
}