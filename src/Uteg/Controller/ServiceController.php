<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends DefaultController {
    /**
     * @Route("{compid}/grouping", name="grouping")
     * @Method("GET")
     */
    public function clubsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();
        $module->grouping($request, $comp);
    }
}