<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Club;
use uteg\Form\Type\ClubType;

class JudgeController extends DefaultController
{
    /**
     * @Route("/judging", name="judging")
     * @Method("GET")
     */
    public function judgingAction(Request $request)
    {
        $this->get('acl_competition')->isGrantedUrl('IS_AUTHENTICATED_FULLY', false);

        $comps = array();

        $user = $this->getUser();
        $authorizationChecker = $this->get('security.authorization_checker');

        foreach ($user->getJ2cs() as $j2c) {
            $request->getSession()->set('comp', $j2c->getCompetition()->getId());
            if ($authorizationChecker->isGranted('VIEW', $j2c->getCompetition())) {
                $j2cs[] = $j2c;
            }
        }

        return $this->render('judging.html.twig', array(
            "j2cs" => $j2cs
        ));
    }
}