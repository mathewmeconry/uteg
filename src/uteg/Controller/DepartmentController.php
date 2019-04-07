<?php

namespace uteg\Controller;

use Doctrine\Common\Util\ClassUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Department;
use uteg\Entity\DivisionEGT;
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

        $dateFormatter = $this->get('bcc_extra_tools.date_formatter');
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $translator = $this->container->get('translator');

        $deps = $comp->getDepartments();
        $departments["data"] = array();

        foreach ($deps as $dep) {
            if($dep->getStarted() === false && $dep->getEnded() == false) {
                $state = '<button class="btn btn-primary btn-condensed btn-icon start" data-competitionPlace="'.$dep->getCompetitionPlace().'"><i class="fa fa-play"></i><span>'.$translator->trans('departments.start', array(), 'uteg').'</span></button>';
            } elseif($dep->getStarted() === true && $dep->getEnded() == false) {
                $state = '<button class="btn btn-warning btn-condensed btn-icon"><i class="fa fa-hourglass-half"></i><span>'.$translator->trans('departments.running', array(), 'uteg').'</span></button>';;
            } else {
                $state = '<button class="btn btn-success btn-condensed btn-icon"><i class="fa fa-check"></i><span>'.$translator->trans('departments.finished', array(), 'uteg').'</span></button>';;
            }
            $departments["data"][] = array("id" => $dep->getId(),
                "number" => $dep->getNumber(),
                "date" => $dateFormatter->format($dep->getDate(), "medium", "none", $request->getPreferredLanguage()),
                "competitionPlace" => $dep->getCompetitionPlace(),
                "category" => $dep->getCategory()->getName(),
                "gender" => $dep->getGender(),
                "state" => $state
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
        $module = $this->get($competition->getModule()->getServiceName());
        $dateFormatter = $this->get('bcc_extra_tools.date_formatter');
        $interval = new \DateInterval('P1D'); // 1 Day
        $dateRange = new \DatePeriod($competition->getStartdate(), $interval, $competition->getEnddate()->modify('+1 day'));

        $dateList = [];
        foreach ($dateRange as $date) {
            $dateList[$dateFormatter->format($date, "short", "none", "en")] = $dateFormatter->format($date, "short", "none", $request->getPreferredLanguage());
        }

        $competitionPlaceList = [];
        for($i = 1; $i <= $competition->getCountCompetitionPlace(); $i++) {
            $competitionPlaceList[$i] = $i;
        }

        $department = new Department();
        $form = $this->container->get('form.factory')->create(new DepartmentType($competitionPlaceList, $dateList, $dateFormatter->getPattern("short", "none", $request->getPreferredLanguage())), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $department = $form->getData();

            $department->setDate(new \DateTime($department->getDate()));
            $department->setCompetition($competition);
            $department->setStarted(false);
            $department->setEnded(false);
            $department->setRound(0);

            for($i = 1; $i < 5;$i++){
                $division = $module->getDivision();
                $division->setDevice($em->getRepository('uteg:Device')->findOneById($i));
                $division->setDepartment($department);
                $em->persist($division);
            }

            if($department->getGender() === "male") {
                $division = $module->getDivision();
                $division->setDevice($em->getRepository('uteg:Device')->findOneById(5));
                $division->setDepartment($department);
                $em->persist($division);
            }

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
            $dateList[$dateFormatter->format($date, "short", "none", "en")] = $dateFormatter->format($date, "short", "none", $request->getPreferredLanguage());
        }

        $competitionPlaceList = [];
        for($i = 1; $i <= $competition->getCountCompetitionPlace(); $i++) {
            $competitionPlaceList[$i] = $i;
        }

        $department = $em->find('uteg:Department', $id);
        $department->setDate($dateFormatter->format($department->getDate(), "short", "none", $request->getPreferredLanguage()));

        $form = $this->container->get('form.factory')->create(new DepartmentType($competitionPlaceList, $dateList, $dateFormatter->getPattern("short", "none", $request->getPreferredLanguage()), true), $department);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $uow = $em->getUnitOfWork();
            $department = $form->getData();
            $oldDepartment = $uow->getOriginalEntityData($department);

            $department->setDate(new \DateTime($department->getDate()));
            if ($oldDepartment['category'] !== $department->getCategory() || $oldDepartment['date']->format('Y-m-d') !== $department->getDate()->format('Y-m-d') || $oldDepartment['gender'] !== $department->getGender()) {
                $this->adjustDepNumbering($competition, $oldDepartment, 'down');
            } else {
                $em->persist($department);
                $em->flush();
            }

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

    /**
     * @Route("{compid}/start/{dep}", name="departmentStart", defaults={"dep": ""})
     * @Method("POST")
     */
    public function startDepartmentAction(Request $request, $compid, $dep) {
        $em = $this->getDoctrine()->getManager();
        $department = $em->getRepository('uteg:Department')->find($dep);

        $department->setStarted(true);
        $em->persist($department);
        $em->flush();

        $this->container->get('session')->getFlashBag()->add('success', 'department.start.success');
        return new Response('true');
    }

    private function adjustDepNumbering(\uteg\Entity\Competition $competition, $srcDepartment, $mode)
    {
        if (is_array($srcDepartment)) {
            $departments = $competition->getDepartmentsByCatDateGender($srcDepartment['category'], $srcDepartment['date'], $srcDepartment['gender']);
        } else {
            $departments = $competition->getDepartmentsByCatDateGender($srcDepartment->getCategory(), $srcDepartment->getDate(), $srcDepartment->getGender());
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

                    if ($department->getNumber() >= $srcNumber) {
                        $department->setNumber($department->getNumber() - 1);
                    } else {
                        $department->setNumber($department->getNumber() + 1);
                    }

                    $em->persist($department);
                }
                break;
        }

        $em->flush();
    }

}