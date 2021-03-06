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
        $menu->addChild('egt.nav.grades', array('uri' => '#', 'icon' => 'graduation-cap', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        for ($i = 1; $i <= $event->getEntityManager()->getRepository('uteg:Competition')->find($event->getRequest()->get('compid'))->getCountCompetitionPlace(); $i++) {
            $menu['egt.nav.grades']->addChild($event->getTranslator()->trans('egt.nav.judging.competitionPlace', array('%number%' => $i), 'uteg'), array('route' => 'enterGrades', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'competitionPlace' => $i)));
        }
        $menu->addChild('egt.nav.judges', array('route' => 'judges', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => 'gavel', 'labelAttributes' => array('class' => 'xn-text')));
        $menu->addChild('Championat', array('route' => 'championat', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'deviceid' => 1, 'limit' => 6), 'icon' => '', 'labelAttributes' => array('class' => 'xn-text')));
        $menu->addChild('egt.nav.grouping', array('uri' => '#', 'icon' => 'object-group', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        $menu['egt.nav.grouping']->addChild('egt.nav.departments', array('route' => 'department', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => ''));
        $menu['egt.nav.grouping']->addChild('egt.nav.divisions', array('route' => 'division', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => ''));
    }

    public function onAddReportingMenu(MenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu['nav.reporting']->addChild('egt.nav.grouping', array('route' => 'reportingDivisions', 'routeParameters' => array('compid' => $event->getRequest()->get('compid')), 'icon' => 'object-group'));
        $menu['nav.reporting']->addChild('egt.nav.ranking', array('uri' => '#', 'icon' => 'trophy', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        $menu['nav.reporting']['egt.nav.ranking']->addChild('egt.nav.ranking.male', array('route' => 'reportingRanking', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'gender' => 'male'), 'icon' => 'male'));
        $menu['nav.reporting']['egt.nav.ranking']->addChild('egt.nav.ranking.female', array('route' => 'reportingRanking', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'gender' => 'female'), 'icon' => 'female'));
        $menu['nav.reporting']->addChild('egt.nav.judging', array('uri' => '#', 'icon' => 'gavel', 'attributes' => array('class' => 'xn-openable'), 'labelAttributes' => array('class' => 'xn-text')));
        for ($i = 1; $i <= $event->getEntityManager()->getRepository('uteg:Competition')->find($event->getRequest()->get('compid'))->getCountCompetitionPlace(); $i++) {
            $menu['nav.reporting']['egt.nav.judging']->addChild($event->getTranslator()->trans('egt.nav.judging.competitionPlace', array('%number%' => $i), 'uteg'), array('route' => 'judgingReport', 'routeParameters' => array('compid' => $event->getRequest()->get('compid'), 'competitionPlace' => $i, 'format' => 'pdf')));
        }
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
                                "competitionPlace" => $dep->getCompetitionPlace(),
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
                            $array['value'][$dep->getDate()->getTimestamp()][$dep->getCompetitionPlace()][$dep->getId()] = array("id" => $dep->getId(),
                                "number" => $dep->getNumber(),
                                "date" => $dateFormatter->format($dep->getDate(), "short", "none", $request->getPreferredLanguage()),
                                "competitionPlace" => $dep->getCompetitionPlace(),
                                "category" => $dep->getCategory()->getName(),
                                "gender" => $dep->getGender()
                            );
                        }
                    }

                    ksort($array['value']);
                    foreach ($array['value'] as $key => $day) {
                        foreach ($day as $key2 => $compPlace) {
                            usort($compPlace, function ($a, $b) {
                                return $a['number'] - $b['number'];
                            });

                            $return['value'][$key]['deps'][$key2]['deps'] = $compPlace;
                            $return['value'][$key]['deps'][$key2]['competitionPlace'] = end($compPlace)['competitionPlace'];
                            $return['value'][$key]['date'] = end($compPlace)['date'];
                        }
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
                    $return['value']['assigned'] = $assignedS2cs;
                    $return['value']['unassigned'] = $unassignedS2cs;
                } else {
                    $return = "";
                }
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
            return $this->renderPdf('DivisionReport', 'egt/reporting/divisionsReport.html.twig', array(
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
        $starters = $this->genRanking($starters, 30);
        $highGrades = $this->getHighestGrades($starters, $gender);

        $headers = array(array('name' => 'egt.reporting.ranking.rank', 'style' => 'width: 5px;'),
            array('name' => 'egt.reporting.ranking.firstname', 'style' => ''),
            array('name' => 'egt.reporting.ranking.lastname', 'style' => ''),
            array('name' => 'egt.reporting.ranking.birthyear', 'style' => ''),
            array('name' => 'egt.reporting.ranking.club', 'style' => ''),
            array('name' => 'device.floorShort', 'style' => 'width: 40px;text-align: center;'),
            array('name' => 'device.ringsShort', 'style' => 'width: 40px;text-align: center;'),
            array('name' => 'device.vaultShort', 'style' => 'width: 40px;text-align: center;'));

        if ($gender === "male") {
            $headers[] = array('name' => 'device.parallel-barsShort', 'style' => 'width: 40px;text-align: center;');
        }

        $headers[] = array('name' => 'device.horizontal-barShort', 'style' => 'width: 40px;text-align: center;');
        $headers[] = array('name' => 'egt.reporting.ranking.total', 'style' => 'width: 40px;text-align: center;');
        $headers[] = array('name' => 'egt.reporting.ranking.award', 'style' => 'width: 5px;');

        if ($format === "pdf") {
            return $this->renderPdf('Ranking', 'egt/reporting/rankingReport.html.twig', array(
                "competition" => $competition,
                "category" => $category,
                "starters" => $starters,
                "headers" => $headers,
                "highGrades" => $highGrades
            ));
        } elseif ($format === "ajax") {
            return $this->container->get('templating')->renderResponse('egt/reporting/rankingReport.html.twig', array(
                "competition" => $competition,
                "category" => $category,
                "starters" => $starters,
                "headers" => $headers,
                "highGrades" => $highGrades
            ));
        }

        $categories = $this->getUsedCategories($competition);

        return $this->container->get('templating')->renderResponse('egt/reporting/ranking.html.twig', array(
            "categories" => $categories,
            "gender" => $gender
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

    public function judging(Request $request, \uteg\Entity\Competition $competition, \uteg\Entity\Device $device, $competitionPlace)
    {
        if ($competitionPlace > 0) {
            $judgingArr = $this->generateJudgingArray($device, $competition, $competitionPlace);
        } else {
            $judgingArr['starters'] = [];
            $judgingArr['devices'] = [];
            $judgingArr['round'] = 0;
        }

        if (isset($judgingArr['error'])) {
            return $this->container->get('templating')->renderResponse('egt/judging.html.twig', $this->getJudgingOptions($competition, $device, $competitionPlace));
        }
        return $this->container->get('templating')->renderResponse('egt/judging.html.twig', $this->getJudgingOptions($competition, $device, $competitionPlace));
    }

    public function judgingReport(Request $request, \uteg\Entity\Competition $competition, $competitionPlace, $format)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $devices = array(1 => $em->find('uteg:Device', 1),
            2 => $em->find('uteg:Device', 2),
            3 => $em->find('uteg:Device', 3),
            4 => $em->find('uteg:Device', 4));
        $judgingArr = [];

        $departments = $em
            ->getRepository('uteg:Department')
            ->createQueryBuilder('d')
            ->select('d.gender as gender')
            ->where('d.started = 1')
            ->andWhere('d.ended = 0')
            ->andWhere('d.competition = :competition')
            ->andWhere('d.competitionPlace = :competitionPlace')
            ->setParameters(array('competition' => $competition->getId(), 'competitionPlace' => $competitionPlace))
            ->getQuery()->getResult();

        foreach ($departments as $department) {
            if ($department['gender'] === "male") {
                $devices[5] = $em->find('uteg:Device', 5);
            }
        }

        foreach ($devices as $device) {
            $judgingArr[$device->getNumber()] = array("starters" => $this->generateJudgingArray($device, $competition, $competitionPlace), "devicename" => $device->getName());
        }

        ksort($judgingArr);

        if ($format === "pdf") {
            return $this->renderPdf('Judging', 'egt/reporting/judgingReport.html.twig', array(
                "competition" => $competition,
                "devices" => $judgingArr
            ));
        }

        return $this->container->get('templating')->renderResponse('egt/reporting/judgingReport.html.twig', array(
            "competition" => $competition,
            "devices" => $judgingArr
        ));
    }

    public function saveGrades(\uteg\Entity\Competition $competition, \uteg\Entity\Device $device, $grades, $numberFormatLenght = 2, $deepValidate = true)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $error = array();

        foreach ($grades as $grade) {
            $s2c = $em->getRepository('uteg:Starters2Competitions')->find($grade['s2c']);
            $realGrade = number_format($grade['grade'], $numberFormatLenght, '.', '');

            $gradeEntity = new Grade();
            $gradeEntity->setId($competition->getId() . $s2c->getId() . $device->getNumber());
            $gradeEntity->setS2c($s2c);
            $gradeEntity->setCompetition($competition);
            $gradeEntity->setDevice($device);
            $gradeEntity->setGrade($realGrade);
            $error[$grade['s2c']] = $this->saveGrade($gradeEntity, $deepValidate);
        }

        return new Response(json_encode($error));
    }

    public function enterGrades(Request $request, \uteg\Entity\Competition $competition, $competitionPlace)
    {
        $startersMerge = [];

        for ($i = 1; $i < 6; $i++) {
            $device = $this->container->get('doctrine')->getEntityManager()->find('uteg:Device', $i);
            $baseMerge[$i] = $this->getJudgingOptions($competition, $device, $competitionPlace);
        }

        $rounds = $baseMerge[1]['devices'];
        ksort($rounds);
        $baseData = $baseMerge[1];
        $baseData['starters'] = [];
        $baseData['devices'] = [];

        $cleanedMerge = [];
        $a = 0;
        foreach ($rounds as $i) {
            $baseData['avDevices'][$a] = $this->container->get('doctrine')->getEntityManager()->getRepository('uteg:Device')->findBy(Array("number" => $i))[0];
            $cleanedMerge[$a] = $baseMerge[$i];
            $cleanedMerge[$a]['devices'] = [];

            $c = 0;
            foreach ($baseMerge[$i]['devices'] as $devices) {
                $cleanedMerge[$a]['devices'][$c] = $devices;
                $c++;
            }
            $a++;
        }


        for ($i = 0; $i < count($cleanedMerge); $i++) {
            $baseData['devices'][$i] = $cleanedMerge[$i]['devices'];
            $baseData['starters'][$i] = $cleanedMerge[$i]['starters'];
        }


        return $this->container->get('templating')->renderResponse('egt/enterGrades.html.twig', Array(
            'data' => $baseData
        ));
    }

    public function turn(Request $request, \uteg\Entity\Competition $competition, $competitionPlace)
    {
        $deps = $this->getDepartments($competition, $competitionPlace);
        $em = $this->container->get('doctrine')->getEntityManager();
        $depRepo = $em->getRepository('uteg:Department');
        $ended = false;


        foreach ($deps as $dep) {
            $depEntity = $depRepo->findOneBy(array("id" => $dep['id']));
            $depEntity->increaseRound();
            if ($dep['round'] + 1 === 4 && $dep['gender'] === "female") {
                $depEntity->setEnded(1);
                $ended = true;
            } elseif ($dep['round'] + 1 === 5 && $dep['gender'] === "male") {
                $depEntity->setEnded(1);
                $ended = true;
            }
            $em->persist($depEntity);
        }
        $em->flush();
        return new Response(json_encode($ended));
    }

    private function getJudgingOptions(\uteg\Entity\Competition $competition, \uteg\Entity\Device $device, $competitionPlace)
    {
        if ($competitionPlace > 0) {
            $judgingArr = $this->generateJudgingArray($device, $competition, $competitionPlace);
        } else {
            $judgingArr['starters'] = [];
            $judgingArr['devices'] = [];
            $judgingArr['round'] = 0;
        }

        if (isset($judgingArr['error'])) {
            return array(
                "compid" => $competition->getId(),
                "device" => $device,
                "deviceid" => $device->getId(),
                "starters" => array(),
                "devices" => array(),
                "round" => array(),
                "error" => $judgingArr['error'],
                "countCompetitionPlace" => $competition->getCountCompetitionPlace(),
                "competitionPlace" => $competitionPlace
            );
        }
        return array(
            "compid" => $competition->getId(),
            "device" => $device,
            "deviceid" => $device->getId(),
            "starters" => $judgingArr['starters'],
            "devices" => $judgingArr['devices'],
            "round" => $judgingArr['round'],
            "countCompetitionPlace" => $competition->getCountCompetitionPlace(),
            "competitionPlace" => $competitionPlace
        );
    }

    private function generateJudgingArray(\uteg\Entity\Device $device, \uteg\Entity\Competition $competition, $competitionPlace)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $devices = array(1 => 1, 2 => 2, 3 => 3, 4 => 5, 5 => 4);

        $departments = $em
            ->getRepository('uteg:Department')
            ->createQueryBuilder('d')
            ->select('d.round as round')
            ->where('d.started = 1')
            ->andWhere('d.ended = 0')
            ->andWhere('d.competition = :competition')
            ->andWhere('d.competitionPlace = :competitionPlace')
            ->setParameters(array('competition' => $competition->getId(), 'competitionPlace' => $competitionPlace))
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
            ->andWhere('s.present = 1')
            ->andWhere('de.competition = :competition')
            ->andWhere('de.competitionPlace = :competitionPlace')
            ->addOrderBy('ca.id', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->setParameters(array('competition' => $competition->getId(), 'competitionPlace' => $competitionPlace))
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

                if (array_key_exists($device, $return)) {
                    $startersDevice = $return[$device];
                }

                if (isset($startersDevice)) {
                    if ($round > count($startersDevice)) {
                        $round -= count($startersDevice);
                    }

                    $splice = array_splice($startersDevice, 0, $round);
                    $return[$device] = array_merge($startersDevice, $splice);
                } else {
                    $return[$device] = Array('pause');
                }

                unset($startersDevice);
                $round++;
            }

            return array("devices" => $devices, "starters" => $return, "round" => $departments[0]['round'] + 1);
        } else {
            return array("error" => "notStarted");
        }
    }

    private function saveGrade(\uteg\Entity\Grade $grade, $deepValidate = true)
    {
        $em = $this->container->get('Doctrine')->getManager();

        if ($deepValidate) {
            $float = explode('.', $grade->getGrade());
            if (!isset($float[1])) {
                $float[1] = 00;
            }
            $float = $float[1] % 5;
        }

        if ($grade->getGrade() >= 0 && $grade->getGrade() <= 10) {
            if ($deepValidate) {
                if ($float === 0) {
                    $startDevice = $grade->getS2c()->getDivision()->getDevice()->getId();
                    $gender = $grade->getS2c()->getStarter()->getGender();
                    $round = $grade->getS2c()->getDivision()->getDepartment()->getRound();
                    $rotated = $this->rotate($startDevice, $round, $gender);

                    $em->merge($grade);
                    $em->flush();
                    return array('ok');
                } else {
                    return array('invalidGrade', $this->container->get('translator')->trans('egt.judging.invalidGrade', array(), 'uteg'));
                }
            } else {
                $em->merge($grade);
                $em->flush();

                return array('ok');
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

/*         if ($gender === "male") {
            if ($device === 5) {
                $device = 4;
            } elseif ($device === 4) {
                $device = 5;
            }
        } */

        return $device;
    }

    private function renderPdf($filename, $path, $additional)
    {
        $html = $this->container->get('templating')->render($path, $additional);
        $pdf = $this->container->get('knp_snappy.pdf');
        $pdf->setOption('orientation', 'portrait');
        $pdf->setOption('footer-center', '[page] / [topage]');
        $pdf->setoption('footer-right', 'Generated by uteg');
        $pdf->setOption('footer-font-name', 'Quicksand');
        $pdf->setOption('footer-font-size', 8);
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"'
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

    private function getUsedCategories(\uteg\Entity\Competition $competition)
    {
        $em = $this->container->get('doctrine')->getManager();

        return $em
            ->getRepository('uteg:Starters2Competitions')
            ->createQueryBuilder('s2c')
            ->select('c.name as name, c.number as number, c.id as id')
            ->join('s2c.category', 'c')
            ->andWhere('s2c.competition = :competition')
            ->andWhere('s2c.present = 1')
            ->groupBy('c.name')
            ->setParameters(array('competition' => $competition->getId()))
            ->getQuery()->getResult();
    }

    private function getRankingArray(\uteg\Entity\Competition $competition, \uteg\Entity\Category $category, $gender)
    {
        $em = $this->container->get('doctrine')->getManager();
        $deps = $competition->getDepartmentsByCatGender($category, $gender);
        $running = false;
        $round = 0;

        foreach ($deps as $dep) {
            if ($dep->getStarted() && !$dep->getEnded()) {
                $running = true;
                $round = $dep->getRound();
            }
        }
        $default = array(1 => number_format((float)"0.00", 2, '.', ''), 2 => number_format((float)"0.00", 2, '.', ''), 3 => number_format((float)"0.00", 2, '.', ''), 4 => number_format((float)"0.00", 2, '.', ''));

        if ($gender === "male") {
            $default[5] = number_format((float)"0.00", 2, '.', '');
        }

        $starters = $em
            ->getRepository('uteg:Starters2CompetitionsEGT')
            ->createQueryBuilder('s2c')
            ->select('s2c.id as s2cid, s.firstname as firstname, s.lastname as lastname, s.birthyear as birthyear, s.gender as gender, c.name as club')
            ->join('s2c.starter', 's')
            ->join('s2c.club', 'c')
            ->where('s2c.competition = :competition')
            ->andWhere('s2c.category = :category')
            ->andWhere('s.gender = :gender')
            ->andWhere('s2c.present = 1')
            ->setParameters(array('competition' => $competition->getid(), 'category' => $category->getId(), 'gender' => $gender))
            ->getQuery()->getResult();

        foreach ($starters as $key => $starter) {
            $grades = $em
                ->getRepository('uteg:Grade')
                ->createQueryBuilder('g')
                ->select('g.grade as grade, d.number as dnumber, d.name as device')
                ->join('g.device', 'd')
                ->where('g.s2c = :s2c')
                ->orderBy('g.created', 'ASC')
                ->setParameter('s2c', $starter['s2cid']);

            if ($running) {
                $grades->setMaxResults($round);
            }

            $grades = $grades->getQuery()->getResult();

            $sum = 0;

            foreach ($grades as $grade) {
                if ($category->getId() !== 9) {
                    $grade['grade'] = number_format((float)$grade['grade'], 2, '.', '');
                } else {
                    $grade['grade'] = number_format((float)$grade['grade'], 3, '.', '');
                }
                $starter[$grade['dnumber']] = $grade['grade'];
                $sum += $grade['grade'];
            }

            $starter = array_replace($default, $starter);
            if ($category->getId() === 9) {
                $starter['total'] = number_format((float)$sum, 3, '.', '');;;
            } else {
                $starter['total'] = number_format((float)$sum, 2, '.', '');;
            }
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
            $horizontalbar[$key] = $starter[4];
            $total[$key] = $starter['total'];

            if ($gender === "male") {
                $parallelbars[$key] = $starters[5];
            }
        }

        usort($starters, array($this, "sortRankingFunction"));

        return $starters;
    }

    private function genRanking($starters, $percent)
    {
        $awards = round(count($starters) / 100 * $percent);
        $before = 0;
        $add = 1;
        $rank = 0;

        foreach ($starters as $key => $starter) {
            if (bccomp($before, $starter['total'], 2) === 0) {
                $starters[$key]['rank'] = $rank;
                $add++;
            } else {
                $rank += $add;
                $add = 1;
                $starters[$key]['rank'] = $rank;
            }

            if ($rank <= $awards) {
                switch ($rank) {
                    case 1:
                        $starters[$key]['award'] = 'G';
                        break;
                    case 2:
                        $starters[$key]['award'] = 'S';
                        break;
                    case 3:
                        $starters[$key]['award'] = 'B';
                        break;
                    default:
                        $starters[$key]['award'] = '*';
                        break;
                }
            } else {
                $starters[$key]['award'] = '';
            }

            $before = $starter['total'];
        }

        return $starters;
    }

    private function getHighestGrades($starters, $gender)
    {
        $highGrades = array(1 => number_format((float)"0.00", 2, '.', ''), 2 => number_format((float)"0.00", 2, '.', ''), 3 => number_format((float)"0.00", 2, '.', ''), 4 => number_format((float)"0.00", 2, '.', ''), 5 => number_format((float)"0.00", 2, '.', ''));

        if ($gender === "male") {
            $highGrades[4] = number_format((float)"0.00", 2, '.', '');
        }

        foreach ($starters as $starter) {
            (bccomp($highGrades[1], $starter[1], 2) === -1) ? $highGrades[1] = number_format((float)$starter[1], 2, '.', '') : number_format((float)$highGrades[1], 2, '.', '');
            (bccomp($highGrades[2], $starter[2], 2) === -1) ? $highGrades[2] = number_format((float)$starter[2], 2, '.', '') : number_format((float)$highGrades[2], 2, '.', '');
            (bccomp($highGrades[3], $starter[3], 2) === -1) ? $highGrades[3] = number_format((float)$starter[3], 2, '.', '') : number_format((float)$highGrades[3], 2, '.', '');
            if ($gender === "male") {
                (bccomp($highGrades[5], $starter[5], 2) === -1) ? $highGrades[5] = number_format((float)$starter[5], 2, '.', '') : number_format((float)$highGrades[5], 2, '.', '');
            }
            (bccomp($highGrades[4], $starter[4], 2) === -1) ? $highGrades[4] = number_format((float)$starter[4], 2, '.', '') : number_format((float)$highGrades[4], 2, '.', '');
        }

        return $highGrades;
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
            if ($c === 4 && $a['gender'] === "male") {
                $c = 5;
            }

            if ($c === 5) {
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
        ksort($starters);
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

                if ($groupBy !== "category") {
                    unset($value[$groupBy]);
                }

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

    private function getDepartments($comp, $competitionPlace)
    {
        return $this->container->get('doctrine')->getEntityManager()
            ->getRepository('uteg:Department')
            ->createQueryBuilder('d')
            ->select('d.id as id, d.round as round, d.gender as gender')
            ->where('d.started = 1')
            ->andWhere('d.ended = 0')
            ->andWhere('d.competition = :competition')
            ->andWhere('d.competitionPlace = :competitionPlace')
            ->setParameters(array('competition' => $comp, 'competitionPlace' => $competitionPlace))
            ->getQuery()->getResult();
    }


    public function generateCampionat(\uteg\Entity\Competition $comp, \uteg\Entity\Device $device, $limit, $format)
    {
        $em = $this->container->get('doctrine')->getEntityManager();

        $queryResult = $em->getRepository('uteg:Grade')
            ->createQueryBuilder('g')
            ->select('s2c.id as id, s.firstname, s.lastname, c.name as club, cat.name as category, g.grade')
            ->join('g.s2c', 's2c')
            ->join('s2c.starter', 's')
            ->join('s2c.club', 'c')
            ->join('s2c.category', 'cat')
            ->where('cat.id >= 6')
            ->andWhere('cat.id <= 8')
            ->andWhere('g.device = :device')
            ->andWhere('s2c.competition = :competition')
            ->orderBy('g.grade', 'DESC')
            ->setMaxResults($limit)
            ->setParameters(array('device' => $device, 'competition' => $comp))
            ->getQuery()->getResult();

        if ($format === "pdf") {
            return $this->renderPdf('Championat', 'egt/reporting/championatReport.html.twig',
                array('starters' => $queryResult)
            );
        } else {
            return $this->container->get('templating')->renderResponse('egt/championat.html.twig',
                array('starters' => $queryResult)
            );
        }
    }

    public function saveChampionatGrades(\uteg\Entity\Competition $competition, \uteg\Entity\Device $device, $grades)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $championat = $em->getRepository('uteg:Category')->find(9);

        foreach ($grades as $key => $grade) {
            $s2c = $em->getRepository('uteg:Starters2Competitions')->find($grade['s2c']);

            $newS2c = clone $s2c;
            $newS2c->setCategory($championat);
            $em->persist($newS2c);
            $em->flush();
            echo $newS2c->getId();
            $grades[$key]['s2c'] = $newS2c->getId();
        }
        return $this->saveGrades($competition, $device, $grades, 3, false);
    }
}