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
use Symfony\Component\Security\Core\Util\SecureRandom;
use uteg\ACL\MaskBuilder;
use uteg\Entity\Competition;
use uteg\Entity\DivisionEGT;
use uteg\Entity\Judges2Competitions;
use uteg\Entity\User;
use uteg\Entity\UserInvitation;
use uteg\EventListener\MenuEvent;
use uteg\Entity\Starters2CompetitionsEGT;
use uteg\Form\Type\J2cType;
use uteg\Entity\Grade;

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
        $menu['nav.reporting']->addChild('egt.nav.ranking', array('uri' => '#', 'icon' => 'trophy', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        $menu['nav.reporting']['egt.nav.ranking']->addChild('egt.nav.ranking.male', array('route' => 'reportingRanking', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'gender' => 'male', 'catid' => 1), 'icon' => 'male'));
        $menu['nav.reporting']['egt.nav.ranking']->addChild('egt.nav.ranking.female', array('route' => 'reportingRanking', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'gender' => 'female', 'catid' => 1), 'icon' => 'female'));

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
            "comp" => $competition
        ));
    }

    public function judgesPost(Request $request, Competition $competition)
    {
        $j2cs = $competition->getJ2cs();
        $arr['data'] = [];

        foreach ($j2cs as $j2c) {
            $arr['data'][] = array("id" => $j2c->getId(),
                "firstname" => $j2c->getUser()->getFirstname(),
                "lastname" => $j2c->getUser()->getLastname(),
                "email" => $j2c->getUser()->getEmail(),
                "device" => $this->container->get('translator')->trans($j2c->getDevice()->getName(), array(), 'uteg'));
        }
        $response = new Response(json_encode($arr));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function judgeAdd(Request $request, \uteg\Entity\Competition $competition)
    {
        $acl = $this->container->get('acl_competition');

        $form = $this->container->get('form.factory')->create(new J2cType(false));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $userForm = $form->getData();

            $user = $em->getRepository('uteg:User')->findOneByEmail($userForm['email']);

            $j2c = new Judges2Competitions();
            $j2c->setCompetition($competition);
            $j2c->setDevice($userForm['device']);

            if ($user) {
                $j2c->setUser($user);
            } else {
                $secure = new SecureRandom();

                $invite = new UserInvitation();
                $invite->setEmail($userForm['email']);
                $em->persist($invite);

                $invite->send();

                $user = new User();
                $user->setInvitation($invite);
                $user->setEmail($userForm['email']);
                $user->setPassword(base64_encode($secure->nextBytes(100)));
                $em->persist($user);

                $j2c->setUser($user);

                $em->persist($j2c);
                $em->flush();
            }

            if ($user) {
                $acl->addPermission(MaskBuilder::MASK_JUDGE, array('username' => $user->getEmail()), $competition->getId());

                $em->persist($j2c);
                $em->flush();
            }


            $this->container->get('session')->getFlashBag()->add('success', 'egt.judges.add.success');

            return new Response('true');
        }

        return $this->container->get('templating')->renderResponse('egt/form/judgeEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'judgeAdd'
            )
        );
    }

    public function judgeEdit(Request $request, Competition $competition, Judges2Competitions $judge)
    {
        $j2c = new J2cType();
        $form = $this->container->get('form.factory')->create($j2c, $judge);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $userForm = $form->getData();

            $user = $em->getRepository('uteg:User')->findOneByEmail($userForm['email']);

            $judge->setDevice($userForm['device']);

            if ($user) {
                $em->persist($judge);
                $em->flush();

                $this->container->get('session')->getFlashBag()->add('success', 'egt.judges.add.success');

                return new Response('true');
            }
        }

        return $this->container->get('templating')->renderResponse('egt/form/judgeEdit.html.twig',
            array('form' => $form->createView(),
                'error' => (isset($errorMessages)) ? $errorMessages : '',
                'target' => 'judgeAdd'
            )
        );
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

    public function reportingRanking(Request $request, \uteg\Entity\Competition $competition, \uteg\Entity\Category $category, $gender, $format)
    {
        $starters = $this->getRankingArray($competition, $category, $gender);
        $starters = $this->sortRanking($starters, $gender);

        $headers = array('egt.reporting.ranking.firstname',
            'egt.reporting.ranking.lastname',
            'egt.reporting.ranking.birthyear',
            'egt.reporting.ranking.club',
            'device.floor',
            'device.rings',
            'device.vault');

        if($gender === "male") {
            $headers[] = 'device.parallel-bars';
        }

        $headers[] = 'device.horizontal-bar';

        if ($format === "pdf") {
            return $this->renderPdf('egt/reporting/divisionsReport.html.twig', array(
                "comp" => $competition,
                "category" => $category,
                "starters" => $starters,
                "headers" => $headers
            ));
        }

        return $this->container->get('templating')->renderResponse('egt/reporting/ranking.html.twig', array(
            "comp" => $competition,
            "category" => $category,
            "starters" => $starters,
            "headers" => $headers
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

    public function judging(Request $request, \uteg\Entity\Competition $competition, \uteg\Entity\Device $device, \uteg\Entity\Judges2Competitions $j2c)
    {
        $judgingArr = $this->generateJudgingArray($device, $competition);

        if (isset($judgingArr['error'])) {
            return $this->container->get('templating')->renderResponse('egt/judging.html.twig', array(
                "compid" => $competition->getId(),
                "device" => $j2c->getDevice(),
                "deviceid" => $j2c->getDevice()->getId(),
                "starters" => array(),
                "devices" => array(),
                "round" => array(),
                "error" => $judgingArr['error']
            ));
        }
        return $this->container->get('templating')->renderResponse('egt/judging.html.twig', array(
            "compid" => $competition->getId(),
            "device" => $j2c->getDevice(),
            "deviceid" => $j2c->getDevice()->getId(),
            "starters" => $judgingArr['starters'],
            "devices" => $judgingArr['devices'],
            "round" => $judgingArr['round']
        ));
    }

    public function saveGrades(\uteg\Entity\Competition $competition, \uteg\Entity\Device $device, $grades)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $error = array();

        foreach ($grades as $grade) {
            $s2c = $em->getRepository('uteg:Starters2Competitions')->find($grade['s2c']);
            $realGrade = number_format($grade['grade'], 2, '.', '');

            $gradeEntity = new Grade();
            $gradeEntity->setId($competition->getId() . $s2c->getId() . $device->getNumber());
            $gradeEntity->setS2c($s2c);
            $gradeEntity->setCompetition($competition);
            $gradeEntity->setDevice($device);
            $gradeEntity->setGrade($realGrade);
            $error[$grade['s2c']] = $this->saveGrade($gradeEntity);
        }

        return new Response(json_encode($error));
    }

    private function generateJudgingArray(\uteg\Entity\Device $device, \uteg\Entity\Competition $competition)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $devices = array(1 => 1, 2 => 2, 3 => 3, 4 => 5);

        $departments = $em
            ->getRepository('uteg:Department')
            ->createQueryBuilder('d')
            ->select('d.round as round')
            ->where('d.started = 1')
            ->andWhere('d.ended = 0')
            ->andWhere('d.competition = :competition')
            ->setParameters(array('competition' => $competition->getId()))
            ->getQuery()->getResult();

        $starters = $em
            ->getRepository('uteg:Starters2CompetitionsEGT')
            ->createQueryBuilder('s')
            ->select('s.id as id, st.firstname as firstname, st.lastname as lastname, st.gender as gender, c.name as club, d.number as devicenumber, d.id as deviceid, ca.name as category')
            ->join('s.division', 'di')
            ->join('di.department', 'de')
            ->join('s.starter', 'st')
            ->join('s.club', 'c')
            ->join('di.device', 'd')
            ->join('s.category', 'ca')
            ->where('de.started = 1')
            ->andWhere('de.ended = 0')
            ->andWhere('de.competition = :competition')
            ->setParameters(array('competition' => $competition->getId()))
            ->getQuery()->getResult();

        if ($starters) {
            foreach ($starters as $starter) {
                $return[$starter['devicenumber']][] = $starter;

                if ($starter['gender'] === 'male') {
                    $devices[5] = 4;
                }
            }

            $key = array_search($device->getId(), $devices);
            $slice = array_reverse(array_slice($devices, 0, $key, true), true);
            $devices = array_reverse($devices, true);
            $devices = array_replace($slice, $devices);

            $round = 0;
            foreach ($devices as $key => $device) {
                $startersDevice = $return[$device];

                if ($round > count($startersDevice)) {
                    $round -= count($startersDevice);
                }

                $splice = array_splice($startersDevice, 0, $round);
                $return[$device] = array_merge($startersDevice, $splice);
                $round++;
            }

            return array("devices" => $devices, "starters" => $return, "round" => $departments[0]['round'] + 1);
        } else {
            return array("error" => "notStarted");
        }
    }

    private function saveGrade(\uteg\Entity\Grade $grade)
    {
        $em = $this->container->get('Doctrine')->getManager();

        $float = explode('.', $grade->getGrade());
        if (!isset($float[1])) {
            $float[1] = 00;
        }
        $float = $float[1] % 5;

        if ($grade->getGrade() >= 0 && $grade->getGrade() <= 10 && $float === 0) {
            $startDevice = $grade->getS2c()->getDivision()->getDevice()->getId();
            $gender = $grade->getS2c()->getStarter()->getGender();
            $round = $grade->getS2c()->getDivision()->getDepartment()->getRound();
            $rotated = $this->rotate($startDevice, $round, $gender);

            if ($rotated === $grade->getDevice()->getId()) {
                $em->merge($grade);
                $em->flush();
                return array('ok');
            } else {
                return array('wrongDevice', $this->container->get('translator')->trans('egt.judging.wrongDevice', array(), 'uteg'), $round . "/" . $rotated . "/" . $grade->getDevice()->getId() . "/" . $startDevice);
            }
        } else {
            return array('invalidGrade', $this->container->get('translator')->trans('egt.judging.invalidGrade', array(), 'uteg'));
        }
    }

    private function rotate($device, $round, $gender)
    {
        $device = $device + $round;
        if ($device > 4 && $gender === "female") {
            $device -= 4;
        } elseif ($device > 5 && $gender === "male") {
            $device -= 5;
        }

        if ($gender === "male") {
            if ($device === 5) {
                $device = 4;
            } elseif ($device === 4) {
                $device = 5;
            }
        }

        return $device;
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

    private function getRankingArray(\uteg\Entity\Competition $competition, \uteg\Entity\Category $category, $gender)
    {
        $em = $this->container->get('doctrine')->getManager();

        $starters = $em
            ->getRepository('uteg:Starters2CompetitionsEGT')
            ->createQueryBuilder('s2c')
            ->select('s2c.id as s2cid, s.firstname as firstname, s.lastname as lastname, s.birthyear as birthyear, s.gender as gender, c.name as club')
            ->join('s2c.starter', 's')
            ->join('s2c.club', 'c')
            ->where('s2c.competition = :competition')
            ->andWhere('s2c.category = :category')
            ->andWhere('s.gender = :gender')
            ->setParameters(array('competition' => $competition->getid(), 'category' => $category->getId(), 'gender' => $gender))
            ->getQuery()->getResult();

        foreach ($starters as $key => $starter) {
            $grades = $em
                ->getRepository('uteg:Grade')
                ->createQueryBuilder('g')
                ->select('g.grade as grade, d.number as dnumber, d.name as device')
                ->join('g.device', 'd')
                ->where('g.s2c = :s2c')
                ->setParameter('s2c', $starter['s2cid'])
                ->getQuery()->getResult();

            $sum = 0;

            foreach ($grades as $grade) {
                $starter[$grade['dnumber']] = floatval($grade['grade']);
                $sum += floatval($grade['grade']);
            }

            $starter['total'] = $sum;
            $starters[$key] = $starter;
        }

        return $starters;
    }

    private function sortRanking($starters, $gender)
    {
        foreach ($starters as $key => $starter) {
            $floor[$key] = $starter[1];
            $swiningrings[$key] = $starter[2];
            $vault[$key] = $starter[3];
            $horizontalbar[$key] = $starter[5];
            $total[$key] = $starter['total'];

            if ($gender === "male") {
                $parallelbars[$key] = $starters[4];
            }
        }

        usort($starters, array($this, "sortRankingFunction"));

        return $starters;
    }

    private function sortRankingFunction($a, $b)
    {
        $first = $a['total'];
        $second = $b['total'];
        $c = 1;

        while (bccomp($second, $first, 2) === 0) {
            $first = $a[$c];
            $second = $b[$c];

            $c++;
            if ($c === 4 && $a['gender'] === "female") {
                $c = 5;
            }

            if ($c === 6) {
                return 0;
            }
        }

        return bccomp($second, $first, 2);
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