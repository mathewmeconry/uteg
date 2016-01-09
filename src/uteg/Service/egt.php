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
use uteg\Entity\DivisionEGT;
use uteg\EventListener\MenuEvent;
use uteg\Entity\Starters2CompetitionsEGT;

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

    public function getS2c()
    {
        return new Starters2CompetitionsEGT();
    }

    public function findS2c(Array $searchArray)
    {
        $em = $this->container->get('doctrine')->getManager();

        return $em->getRepository('uteg:Starters2CompetitionsEGT')->findOneBy($searchArray);
    }

    public function getS2cString()
    {
        return 'Starters2CompetitionsEGT';
    }

    public function getDivision()
    {
        return new DivisionEGT();
    }

    public function findDivision(Array $searchArray)
    {
        $em = $this->container->get('doctrine')->getManager();

        return $em->getRepositiry('uteg:DivsionEGT')->findOneBy($searchArray);
    }

    public function getDivisionString()
    {
        return 'DivsionEGT';
    }


    public function divisions(Request $request, Competition $competition)
    {
        return $this->container->get('templating')->renderResponse('egt/divisions.html.twig', array(
            "comp" => $competition
        ));
    }

    public function divisionsFilter(Request $request, Competition $competition)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $data = $request->request->get('divisions-filter');
        $entityName = $request->query->get('by');
        $return = [];
        $return['value'] = [];
        $return['filteredBy'] = $entityName;

        switch ($entityName) {
            case "gender":
                if (array_key_exists('gender', $data)) {
                    $gender = $data['gender'];
                    $deps = $competition->getDepartmentsByGender($gender);
                    foreach ($deps as $dep) {
                        if (!array_key_exists($dep->getCategory()->getNumber(), $return['value'])) {
                            $return['value'][$dep->getCategory()->getNumber()] = array("number" => $dep->getCategory()->getNumber(),
                                "name" => $dep->getCategory()->getName()
                            );
                        }
                    }
                } else {
                    $return = "";
                }

                break;
            case "category":
                if (array_key_exists('category', $data) && array_key_exists('gender', $data)) {
                    $catid = $data['category'];
                    $gender = $data['gender'];
                    $dateFormatter = $this->container->get('bcc_extra_tools.date_formatter');
                    $category = $em->find('uteg:Category', $catid);
                    $deps = $competition->getDepartmentsByCatGender($category, $gender);
                    $array['value'] = [];

                    foreach ($deps as $dep) {
                        if (!array_key_exists($dep->getCategory()->getNumber(), $array['value'])) {
                            $array['value'][$dep->getDate()->getTimestamp()][$dep->getId()] = array("id" => $dep->getId(),
                                "number" => $dep->getNumber(),
                                "date" => $dateFormatter->format($dep->getDate(), "short", "none", $request->getPreferredLanguage()),
                                "category" => $dep->getCategory()->getName(),
                                "gender" => $dep->getGender()
                            );
                        }
                    }

                    ksort($array['value']);
                    foreach ($array['value'] as $key => $day) {
                        usort($day, function ($a, $b) {
                            return $a['number'] - $b['number'];
                        });

                        $return['value'][$key]['deps'] = $day;
                        $return['value'][$key]['date'] = end($day)['date'];
                    }
                } else {
                    $return = "";
                }
                break;
            case "department":
                if (array_key_exists('category', $data) && array_key_exists('gender', $data) && array_key_exists('department', $data)) {
                    $catid = $data['category'];
                    $depid = $data['department'];
                    $gender = $data['gender'];
                    $dateFormatter = $this->container->get('bcc_extra_tools.date_formatter');
                    $category = $em->find('uteg:Category', $catid);
                    $department = $em->find('uteg:Department', $depid);
                    $unassignedS2cs = $competition->getS2csByGenderCat($category, $gender);
                    $assignedS2cs = $competition->getS2csByGenderCatDep($category, $gender, $department);
                    $array['value'] = [];


                } else {
                    $return = "";
                }
                break;
            case "club":

                break;
            default:
                echo "server error";
                break;
        }

        $response = new Response(json_encode((array)$return));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
