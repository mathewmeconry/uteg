<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class JudgeController extends DefaultController
{
    /**
     * @Route("/judging", name="judging")
     * @Method("GET")
     */
    public function judgingAction(Request $request)
    {
        $this->get('acl_competition')->isGrantedUrl('IS_AUTHENTICATED_FULLY', false);

        $j2cs = array();

        $user = $this->getUser();
        $authorizationChecker = $this->get('security.authorization_checker');

        foreach ($user->getJ2cs() as $j2c) {
            if ($authorizationChecker->isGranted('JUDGE', $j2c->getCompetition())) {
                $j2cs[] = $j2c;
            }
        }

        return $this->render('judging.html.twig', array(
            "j2cs" => $j2cs
        ));
    }

    /**
     * @Route("/{compid}/judges", name="judges")
     * @Method("GET")
     */
    public function judgesAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');


    }


    /**
     * @Route("/{compid}/judge", name="judge")
     * @Method("GET")
     */
    public function judgeAction(Request $request, $compid)
    {
        $comp = $this->getDoctrine()->getManager()->find('uteg:Competition', $compid);
        $user = $this->getUser();
        $j2c = $user->getJ2cByComp($comp);

        if(!$j2c) {
            throw new AccessDeniedException();
        }

        return $this->render('base.html.twig');
    }
}