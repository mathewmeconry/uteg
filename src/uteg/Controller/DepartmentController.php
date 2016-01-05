<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Department;
use uteg\Form\Type\DepartmentType;

class DepartmentController extends DefaultController
{
    /**
     * @Route("{compid}/departments", name="department")
     * @Method("GET")
     */
    public function departmentsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_VIEW');

        $competition = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($competition->getModule()->getServiceName());
        $module->init();


        return $this->render('egt/departments.html.twig', array(
            "comp" => $competition
        ));
    }

    /**
     * @Route("{compid}/departments", name="departmentPost")
     * @Method("POST")
     */
    public function postDepartmentsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());


    }

    /**
     * @Route("{compid}/departments/add", name="departmentAdd")
     * @Method("POST")
     */
    public function addDepartmentAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $competition = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);

        $department = new Department();

        $form = $this->container->get('form.factory')->create(new DepartmentType(), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $department = $form->getData();

            $department->setDate(new \DateTime($department->getDate()));

            $competitionDepartments = $competition->getDepartmentsbyCatDateSex($department->getCategory(),$department->getDate(), $department->getSex());
            if (count($competitionDepartments) > 0) {
                $department->setNumber($competitionDepartments[count($competitionDepartments) - 1]->getNumber() + 1);
            } else {
                $department->setNumber(1);
            }

            $department->setCompetition($competition);
            $department->setStarted(false);
            $department->setEnded(false);
            $department->setRound(0);

            $em->persist($department);
            $em->flush();

            $this->container->get('session')->getFlashBag()->add('success', 'egt.department.add.success');

            return new Response('true');
        }

        return $this->render('egt/departmentEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'departmentAdd'
            )
        );
    }

    /**
     * @Route("{compid}/departments/remove", name="departmentRemove")
     * @Method("POST")
     */
    public function removeDepartmentAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

    }
}