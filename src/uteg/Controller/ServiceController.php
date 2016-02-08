<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends DefaultController
{
    /**
     * @Route("{compid}/divisions", name="division")
     * @Method("GET")
     */
    public function divisionsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->divisions($request, $comp);
    }

    /**
     * @Route("{compid}/divisions/filter", name="divisionFilter")
     * @Method("POST")
     */
    public function divisionsFilterAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());

        return $module->divisionsFilter($request, $comp);
    }

    /**
     * @Route("{compid}/division/assign", name="divisionAssign")
     * @Method("POST")
     */
    public function divisionAssignAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());

        return $module->divisionAssign($request, $comp);
    }

    /**
     * @Route("{compid}/reporting/divisions/{format}", name="reportingDivisions", defaults={"format": "html"}, requirements={"format": "html|pdf"})
     * @Method("GET")
     */
    public function reportingDivisionsAction(Request $request, $compid, $format)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->reportingDivisions($request, $comp, $format);
    }

    /**
     * @Route("{compid}/reporting/divisions", name="reportingDivisionsPost")
     * @Method("POST")
     */
    public function reportingDivisionsPostAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());

        return $module->reportingDivisionsPost($request, $comp);
    }
}