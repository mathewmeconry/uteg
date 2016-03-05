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
     * @Route("/{compid}/judges", name="judges", requirements={"compid": "\d+"})
     * @Method("GET")
     */
    public function judgesAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->judges($request, $comp);
    }

    /**
     * @Route("/{compid}/judges", name="judgesPost", requirements={"compid": "\d+"})
     * @Method("POST")
     */
    public function judgesPostAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->judgesPost($request, $comp);
    }

    /**
     * @Route("/{compid}/judge/add", name="judgeAdd", requirements={"compid": "\d+"})
     * @Method("POST")
     */
    public function judgeAddAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->judgeAdd($request, $comp);
    }

    /**
     * @Route("/{compid}/judge/edit/{judgeid}", name="judgeEdit", defaults={"judgeid": ""}, requirements={"judgeid": "\d+", "compid": "\d+"})
     * @Method("POST")
     */
    public function judgeEditAction(Request $request, $compid, $judgeid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $judge = $this->getDoctrine()->getEntityManager()->find('uteg:Judges2Competitions', $judgeid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->judgeEdit($request, $comp, $judge);
    }


    /**
     * @Route("/{compid}/judge", name="judge", requirements={"compid": "\d+"})
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