<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends DefaultController {
    /**
     * @Route("{compid}/departments", name="department")
     * @Method("GET")
     */
    public function departmentsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();


        return $module->departments($request, $comp);
    }

    /**
     * @Route("{compid}/departments/add", name="departmentAdd")
     * @Method("POST")
     */
    public function addDepartmentAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $module->departmentForm($request, $comp);
    }

    /**
     * @Route("{compid}/departments/remove", name="departmentRemove")
     * @Method("POST")
     */
    public function removeDepartmentAction(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

    }

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
}