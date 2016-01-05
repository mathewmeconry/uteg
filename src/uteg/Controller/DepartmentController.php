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


        return $this->render('departments.html.twig', array(
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

        setlocale(LC_TIME, $request->getLocale());
        $dateFormatter = $this->get('bcc_extra_tools.date_formatter');
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);

        $deps = $comp->getDepartments();
        $departments["data"] = array();

        foreach ($deps as $dep) {
            $departments["data"][] = array("id" => $dep->getId(),
                "number" => $dep->getNumber(),
                "date" => $dateFormatter->format($dep->getDate(), "medium", "none", $request->getPreferredLanguage()),
                "category" => $dep->getCategory()->getName(),
                "sex" => $dep->getSex()
            );
        }

        $response = new Response(json_encode($departments));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/{compid}/department/add", name="departmentAdd")
     * @Method("POST")
     */
    public function addDepartmentAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $competition = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $dateFormatter = $this->get('bcc_extra_tools.date_formatter');
        $interval = new \DateInterval('P1D'); // 1 Day
        $dateRange = new \DatePeriod($competition->getStartdate(), $interval, $competition->getEnddate()->modify('+1 day'));

        $dateList = [];
        foreach ($dateRange as $date) {
            $dateList[] = $dateFormatter->format($date, "short", "none", $request->getPreferredLanguage());
        }

        $department = new Department();

        $form = $this->container->get('form.factory')->create(new DepartmentType(), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $department = $form->getData();

            $department->setDate(new \DateTime($department->getDate()));

            $competitionDepartments = $competition->getDepartmentsbyCatDateSex($department->getCategory(), $department->getDate(), $department->getSex());
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

            $this->container->get('session')->getFlashBag()->add('success', 'department.add.success');

            return new Response('true');
        }

        return $this->render('form/departmentEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'departmentAdd',
                'dateList' => $dateList
            )
        );
    }

    /**
     * @Route("/{compid}/department/edit/{id}", name="departmentEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function editDepartmentAction(Request $request, $compid, $id)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $em = $this->getDoctrine()->getEntityManager();
        $competition = $em->find('uteg:Competition', $compid);
        $dateFormatter = $this->get('bcc_extra_tools.date_formatter');
        $interval = new \DateInterval('P1D'); // 1 Day
        $dateRange = new \DatePeriod($competition->getStartdate(), $interval, $competition->getEnddate()->modify('+1 day'));

        $dateList = [];
        foreach ($dateRange as $date) {
            $dateList[] = $dateFormatter->format($date, "short", "none", $request->getPreferredLanguage());
        }

        $department = $em->find('uteg:Department', $id);
        $department->setDate('2015-10-01');

        $form = $this->container->get('form.factory')->create(new DepartmentType(), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $department = $form->getData();

            $department->setDate(new \DateTime($department->getDate()));

            $em->persist($department);
            $em->flush();

            $this->container->get('session')->getFlashBag()->add('success', 'department.edit.success');

            return new Response('true');
        }

        return $this->render('form/departmentEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'departmentAdd',
                'dateList' => $dateList
            )
        );
    }

    /**
     * @Route("/{compid}/department/remove/{id}", name="departmentRemove", defaults={"id": ""}, requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function removeDepartmentAction(Request $request, $compid, $id)
    {
        $this->get('acl_competition')->isGrantedUrl('SETTINGS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

    }
}