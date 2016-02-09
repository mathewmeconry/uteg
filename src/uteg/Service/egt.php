<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 10/10/15
 * Time: 12:45 AM
 */

namespace uteg\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Null;
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
        $this->eventDispatcher->addListener('uteg.addReportingMenu', array($this, 'onAddReportingMenu'));
    }

    public function onAddServiceMenu(MenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild('egt.nav.judges', array('route' => 'judges', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => 'gavel', 'labelAttributes' => array('class' => 'xn-text')));
        $menu->addChild('egt.nav.grouping', array('uri' => '#', 'icon' => 'object-group', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        $menu['egt.nav.grouping']->addChild('egt.nav.departments', array('route' => 'department', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => ''));
        $menu['egt.nav.grouping']->addChild('egt.nav.divisions', array('route' => 'division', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => ''));
    }

    public function onAddReportingMenu(MenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu['nav.reporting']->addChild('egt.nav.grouping', array('route' => 'reportingDivisions', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => 'object-group'));
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

    public function judges(Request $request, Competition $competition)
    {
        return $this->container->get('templating')->renderResponse('egt/judges.html.twig', array(
            "j2cs" => $competition->getJ2cs()
        ));
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
                    $depid = $data['department'];
                    $catid = $data['category'];
                    $gender = $data['gender'];
                    $category = $em->find('uteg:Category', $catid);

                    $queryResult = $em
                        ->getRepository('uteg:Starters2CompetitionsEGT')
                        ->createQueryBuilder('s')
                        ->select('cb.id as id', 'cb.name as name')
                        ->join('s.competition', 'c', 'WITH', 'c.id = :competition')
                        ->join('s.starter', 'st', 'WITH', 'st.gender = :gender')
                        ->join('s.club', 'cb')
                        ->where('s.category = :category')
                        ->setParameters(array('competition' => $competition->getId(),
                            'gender' => $gender,
                            'category' => $category
                        ))
                        ->orderBy('cb.name', 'ASC')
                        ->distinct()
                        ->getQuery()
                        ->getResult();

                    foreach ($queryResult as $club) {
                        $clubEntry['id'] = $club['id'];
                        $clubEntry['name'] = $club['name'];
                        $return['value'][] = $clubEntry;
                    }

                } else {
                    $return = "";
                }
                break;
            case "club":
                if (array_key_exists('category', $data) && array_key_exists('gender', $data) && array_key_exists('department', $data) && array_key_exists('club', $data)) {
                    $catid = $data['category'];
                    $depid = $data['department'];
                    $clubid = $data['club'];
                    $club = ($clubid !== 'all') ? $em->find('uteg:Club', $clubid) : $clubid;
                    $gender = $data['gender'];
                    $category = $em->find('uteg:Category', $catid);
                    $department = $em->find('uteg:Department', $depid);
                    $unassignedS2cs = [];
                    $divisions = $department->getDivisions();
                    $assignedS2cs = [];

                    foreach ($divisions as $division) {
                        if ($club === 'all') {
                            $result = $division->getS2cs()->toArray();
                        } else {
                            $result = $division->getS2csByClub($club);
                        }

                        $assignedS2cs[$division->getDevice()->getNumber()]['starters'] = [];
                        foreach ($result as $s2c) {
                            $assignedS2cs[$division->getDevice()->getNumber()]['starters'][] = array("id" => $s2c->getId(),
                                "firstname" => $s2c->getStarter()->getFirstname(),
                                "lastname" => $s2c->getStarter()->getLastname(),
                                "birthyear" => $s2c->getStarter()->getBirthyear(),
                                "club" => $s2c->getClub()->getName(),
                                "category" => ($s2c->getCategory()->getNumber() == 8) ? ($s2c->getStarter()->getGender() == 'female') ? $s2c->getCategory()->getName() . "D" : $s2c->getCategory()->getName() . "H" : $s2c->getCategory()->getName(),
                                "present" => $s2c->getPresent(),
                                "medicalcert" => $s2c->getMedicalcert()
                            );
                        }

                        $assignedS2cs[$division->getDevice()->getNumber()]['id'] = $division->getId();
                    }

                    $query = $em
                        ->getRepository('uteg:Starters2CompetitionsEGT')
                        ->createQueryBuilder('s')
                        ->select('s')
                        ->join('s.competition', 'c', 'WITH', 'c.id = :competition')
                        ->join('s.starter', 'st', 'WITH', 'st.gender = :gender')
                        ->where('s.division is NULL')
                        ->andWhere('s.category = :category')
                        ->andWhere('s.medicalcert = 0')
                        ->addOrderBy('s.club', 'ASC')
                        ->addOrderBy('st.firstname', 'ASC')
                        ->setParameters(array('competition' => $competition->getId(),
                            'category' => $category,
                            'gender' => $gender
                        ));

                    if ($club !== 'all') {
                        $query
                            ->andWhere('s.club = :club')
                            ->setParameter('club', $club);
                    }

                    $queryResult = $query->getQuery()->getResult();

                    foreach ($queryResult as $s2c) {
                        $unassignedS2cs[] = array("id" => $s2c->getId(),
                            "firstname" => $s2c->getStarter()->getFirstname(),
                            "lastname" => $s2c->getStarter()->getLastname(),
                            "birthyear" => $s2c->getStarter()->getBirthyear(),
                            "club" => $s2c->getClub()->getName(),
                            "category" => ($s2c->getCategory()->getNumber() == 8) ? ($s2c->getStarter()->getGender() == 'female') ? $s2c->getCategory()->getName() . "D" : $s2c->getCategory()->getName() . "H" : $s2c->getCategory()->getName(),
                            "present" => $s2c->getPresent(),
                            "medicalcert" => $s2c->getMedicalcert()
                        );
                    }
                }

                $return['value']['assigned'] = $assignedS2cs;
                $return['value']['unassigned'] = $unassignedS2cs;
                break;
            default:
                echo "server error";
                break;
        }

        $response = new Response(json_encode((array)$return));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function divisionAssign(Request $request, \uteg\Entity\Competition $competition)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $s2cId = $request->request->get('starter');
        $divisionId = $request->request->get('division');

        $s2c = $em->getRepository('uteg:Starters2CompetitionsEGT')->findOneBy(array("id" => $s2cId));
        $division = $em->getRepository('uteg:DivisionEGT')->findOneBy(array("id" => $divisionId));

        if (is_null($division)) {
            $s2c->setDivision($division);
            $em->persist($s2c);
            $em->flush();
            return new Response('true');
        } elseif ($competition->isS2cOf($s2c) && $competition->isDepOf($division->getDepartment())) {
            $s2c->setDivision($division);
            $em->persist($s2c);
            $em->flush();
            return new Response('true');
        } else {
            return new Response('access_denied');
        }
    }

    public function reportingDivisions(Request $request, \uteg\Entity\Competition $competition, $format)
    {
        $groupedStarters = $this->generateDivisionsReport($request, $competition);

        if ($format === "pdf") {
            return $this->renderPdf('egt/reporting/divisionsReport.html.twig', array(
                "comp" => $competition,
                "starters" => $groupedStarters,
                "colspan" => count($this->getLastDim($groupedStarters)),
                "columncount" => json_decode($request->cookies->get('division-report'))[1]
            ));
        }

        $groupings = [];
        $groupings[] = array("value" => "none", "name" => "egt.reporting.divisions.none");
        $groupings[] = array("value" => "gender", "name" => "egt.reporting.divisions.gender");
        $groupings[] = array("value" => "category", "name" => "egt.reporting.divisions.category");
        $groupings[] = array("value" => "club", "name" => "egt.reporting.divisions.club");
        $groupings[] = array("value" => "department", "name" => "egt.reporting.divisions.department");
        $groupings[] = array("value" => "device", "name" => "egt.reporting.divisions.device");

        return $this->container->get('templating')->renderResponse('egt/reporting/divisions.html.twig', array(
            "comp" => $competition,
            "groupings" => $groupings,
            "starters" => $groupedStarters,
            "colspan" => count($this->getLastDim($groupedStarters)),
            "columncount" => json_decode($request->cookies->get('division-report'))[1]
        ));
    }

    public function reportingDivisionsPost(Request $request, \uteg\Entity\Competition $competition)
    {
        $groupedStarters = $this->generateDivisionsReport($request, $competition);

        return $this->container->get('templating')->renderResponse('egt/reporting/divisionsReport.html.twig', array(
            "starters" => $groupedStarters,
            "colspan" => count($this->getLastDim($groupedStarters)),
            "columncount" => json_decode($request->cookies->get('division-report'))[1]
        ));
    }

    private function renderPdf($path, $additional)
    {
        $html = $this->container->get('templating')->render($path, $additional);
        $pdf = $this->container->get('knp_snappy.pdf');
        $pdf->setOption('orientation', 'portrait');
        $pdf->setOption('footer-center', '[page] / [topage]');
        $pdf->setOption('footer-font-name', 'Quicksand');
        $pdf->setOption('footer-font-size', 8);

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="DivisionsReport.pdf"'
            )
        );
    }

    private function generateDivisionsReport(Request $request, \uteg\Entity\Competition $competition)
    {
        $cookies = $request->cookies;

        if ($cookies->has('division-report')) {
            $cookieVal = json_decode($cookies->get('division-report'));
        } else {
            $cookieVal = array(array('gender', 'category', 'department', 'device'), '1');
            $cookie = new Cookie('division-report', json_encode($cookieVal));
            $response = new Response();
            $response->headers->setCookie($cookie);
        }

        return $this->reportingSort($cookieVal[0], $this->getCompleteStarters($competition));
    }

    private function getCompleteStarters($competition)
    {
        $em = $this->container->get('doctrine')->getManager();

        $starters = $em
            ->getRepository('uteg:Starters2CompetitionsEGT')
            ->createQueryBuilder('s')
            ->select('st.firstname as firstname, st.lastname as lastname, st.gender as gender, st.birthyear as birthyear, c.name as club, ca.name as category, d.number as department, de.name as device')
            ->join('s.starter', 'st')
            ->join('s.club', 'c')
            ->join('s.division', 'di')
            ->join('di.department', 'd')
            ->join('d.category', 'ca')
            ->join('di.device', 'de')
            ->where('s.medicalcert = 0')
            ->andWhere('d.competition = ?1')
            ->orderBy('st.gender', 'ASC')
            ->addOrderBy('s.category', 'ASC')
            ->addOrderBy('di.department', 'ASC')
            ->addOrderBy('s.division', 'ASC')
            ->addOrderBy('s.club', 'ASC')
            ->addOrderBy('st.firstname', 'ASC')
            ->addOrderBy('st.lastname', 'ASC')
            ->setParameter(1, $competition)
            ->getQuery()->getResult();

        return $starters;
    }

    private function reportingSort(array $grouping, array $starters)
    {
        foreach ($grouping as $groupBy) {
            if ($groupBy !== "none") {
                $starters = $this->groupByRecursive($starters, $groupBy);
            }
        }
        return $starters;
    }

    private function groupByRecursive(array $values, $groupBy)
    {
        $newarr = [];
        foreach ($values as $key => $value) {
            if ($this->countdim($value) > 1) {
                $newarr[$key] = $this->groupByRecursive($value, $groupBy);
            } else {
                $gb = $value[$groupBy];
                unset($value[$groupBy]);

                if ($groupBy === "department") {
                    $gb = $this->container->get('translator')->trans('egt.reporting.divisions.department', array(), 'uteg') . ' ' . $gb;
                }

                $newarr[$gb][] = $value;
            }
        }

        return $newarr;
    }

    private function countdim($array)
    {
        if (is_array(reset($array))) {
            $return = $this->countdim(reset($array)) + 1;
        } else {
            $return = 1;
        }

        return $return;
    }

    private function getLastDim($array)
    {
        if ($this->countdim($array) > 1) {
            return $this->getLastDim(end($array));
        }
        return $array;
    }
}