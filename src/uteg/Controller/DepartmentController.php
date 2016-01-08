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
            $dateList[$dateFormatter->format($date, "short", "none", $request->getPreferredLanguage())] = $dateFormatter->format($date, "short", "none", $request->getPreferredLanguage());
        }

        $department = new Department();
        $form = $this->container->get('form.factory')->create(new DepartmentType($dateList, $dateFormatter->getPattern("short", "none", $request->getPreferredLanguage())), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $department = $form->getData();

            $department->setDate(new \DateTime($department->getDate()));

            $department->setCompetition($competition);
            $department->setStarted(false);
            $department->setEnded(false);
            $department->setRound(0);

            $this->adjustDepNumbering($competition, $department, 'up');

            $this->container->get('session')->getFlashBag()->add('success', 'department.add.success');

            return new Response('true');
        }

        return $this->render('form/departmentEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'departmentAdd'
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
            $dateList[$dateFormatter->format($date, "short", "none", $request->getPreferredLanguage())] = $dateFormatter->format($date, "short", "none", $request->getPreferredLanguage());
        }

        $department = $em->find('uteg:Department', $id);
        $department->setDate($dateFormatter->format($department->getDate(), "short", "none", $request->getPreferredLanguage()));

        $form = $this->container->get('form.factory')->create(new DepartmentType($dateList, $dateFormatter->getPattern("short", "none", $request->getPreferredLanguage())), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $uow = $em->getUnitOfWork();
            $department = $form->getData();
            $oldDepartment = $uow->getOriginalEntityData($department);

            $department->setDate(new \DateTime($department->getDate()));

            if ($oldDepartment['category'] !== $department->getCategory() || $oldDepartment['date'] !== $department->getDate() || $oldDepartment['sex'] !== $department->getSex()) {
                $this->adjustDepNumbering($competition, $oldDepartment, 'down');
            }

            $this->adjustDepNumbering($competition, $department, 'up');

            $this->container->get('session')->getFlashBag()->add('success', 'department.edit.success');

            return new Response('true');
        }

        return $this->render('form/departmentEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'departmentEdit'
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

        $em = $this->getDoctrine()->getManager();
        $department = $em->find('uteg:Department', $id);

        if (!$comp->isDepOf($department)) {
            return new Response('access_denied');
        } else {
            $this->adjustDepNumbering($comp, $department, 'down');
            $em->remove($department);
            $em->flush();

            $this->container->get('session')->getFlashBag()->add('success', 'department.remove.success');
            return new Response('true');
        }
    }

    private function adjustDepNumbering(\uteg\Entity\Competition $competition, $srcDepartment, $mode)
    {
        if (is_array($srcDepartment)) {
            $departments = $competition->getDepartmentsByCatDateSex($srcDepartment['category'], $srcDepartment['date'], $srcDepartment['sex']);
        } else {
            $departments = $competition->getDepartmentsByCatDateSex($srcDepartment->getCategory(), $srcDepartment->getDate(), $srcDepartment->getSex());
        }
        $em = $this->getDoctrine()->getManager();

        switch ($mode) {
            case 'up':
                if (count($departments) > 0 && $departments[count($departments) - 1]->getId() !== $srcDepartment->getId()) {
                    $srcDepartment->setNumber($departments[count($departments) - 1]->getNumber() + 1);
                } else {
                    $srcDepartment->setNumber(1);
                }

                $em->persist($srcDepartment);
                break;
            case 'down':
                foreach ($departments as $department) {
                    if (is_array($srcDepartment)) {
                        $srcNumber = $srcDepartment['number'];
                    } else {
                        $srcNumber = $srcDepartment->getNumber();
                    }

                    if ($department->getNumber() > $srcNumber) {
                        $department->setNumber($department->getNumber() - 1);
                    }
                    $em->persist($department);
                }
                break;
        }

        $em->flush();
    }

}