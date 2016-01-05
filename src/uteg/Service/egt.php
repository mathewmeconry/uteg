<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 10/10/15
 * Time: 12:45 AM
 */

namespace uteg\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Competition;
use uteg\Entity\Department;
use uteg\EventListener\MenuEvent;
use uteg\Entity\Starters2CompetitionsEGT;
use uteg\Form\Type\DepartmentType;

class egt
{
    private $container;
    private $eventDispatcher;

    public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function init()
    {
        $this->eventDispatcher->addListener('uteg.addServiceMenu', array($this, 'onAddServiceMenu'));
    }

    public function onAddServiceMenu(MenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild('egt.nav.grouping', array('uri' => '#', 'icon' => 'object-group', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        $menu['egt.nav.grouping']->addChild('egt.nav.departments', array('route' => 'department', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => ''));
        $menu['egt.nav.grouping']->addChild('egt.nav.divisions', array('route' => 'division', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => ''));

    }

    public function departments(Request $request, Competition $competition)
    {
        return $this->container->get('templating')->renderResponse('egt/departments.html.twig', array(
            "comp" => $competition
        ));
    }

    public function departmentForm(Request $request, Competition $competition)
    {
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

            $this->container->get('session')->getFlashBag()->add('success', 'department.add.success');

            $response = new Response();
            $response->setContent('true');
            return $response->send();
        }

        return $this->container->get('templating')->renderResponse('egt/departmentEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'departmentAdd'
            )
        );
    }

    public function divisions(Request $request, Competition $competition)
    {
        return $this->container->get('templating')->renderResponse('egt/divisions.html.twig', array(
            "comp" => $competition
        ));
    }

    public function getS2c()
    {
        return new Starters2CompetitionsEGT();
    }

    public function findS2c(Array $searchArray)
    {
        $em = $this->container->get('doctrine')->getEntityManager();

        return $em->getRepository('uteg:Starters2CompetitionsEGT')->findOneBy($searchArray);
    }

    public function getS2cString()
    {
        return 'Starters2CompetitionsEGT';
    }
}
