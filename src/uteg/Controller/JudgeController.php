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

        /** @var $user \uteg\Entity\User */
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
     * @Route("/{compid}/{deviceid}/{competitionPlace}/judge", name="judge", requirements={"compid": "\d+", "deviceid": "\d+", "competitionPlace": "\d+"}, defaults={"competitionPlace" = 0})
     * @Method("GET")
     */
    public function judgeAction(Request $request, $compid, $deviceid, $competitionPlace)
    {
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $device = $this->getDoctrine()->getEntityManager()->find('uteg:Device', $deviceid);
        $module = $this->get($comp->getModule()->getServiceName());
        $user = $this->getUser();
        $j2c = $user->getJ2cByComp($comp);

        if(!$j2c) {
            throw new AccessDeniedException();
        }

        if($j2c->getDevice()->getId() !== $device->getId()) {
            throw new AccessDeniedException();
        }

        return $module->judging($request, $comp, $device, $competitionPlace);
    }

    /**
     * @Route("/{compid}/grades/save/{deviceid}", name="saveGrades", defaults={"deviceid": ""}, requirements={"compid": "\d+"})
     * @Method("POST")
     */
    public function saveGradesAction(Request $request, $compid, $deviceid) {
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $device = $this->getDoctrine()->getEntityManager()->getRepository('uteg:Device')->findOneBy(array("id" => $deviceid));;
        $module = $this->get($comp->getModule()->getServiceName());
        $grades = $request->request->get('grades');

        return $module->saveGrades($comp, $device, $grades);
    }

    /**
     * @Route("/{compid}/{competitionPlace}/judging/report/{format}", name="judgingReport", defaults={"format": "html", "competitionPlace" : 0}, requirements={"compid": "\d+", "competitionPlace": "\d+", "format": "pdf|html"})
     * @Method("GET")
     */
    public function judgingReportAction(Request $request, $compid, $competitionPlace, $format) {
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());

        return $module->judgingReport($request, $comp, $competitionPlace, $format);
    }
}