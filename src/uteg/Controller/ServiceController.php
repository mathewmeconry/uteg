<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends DefaultController {
    /**
     * @Route("{compid}/grouping/{sex}", defaults={"sex": "null"}, name="grouping")
     * @Method("GET")
     */
    public function groupingAction(Request $request, $compid, $sex)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();


        return $module->grouping($request, $comp, $sex);
    }
}