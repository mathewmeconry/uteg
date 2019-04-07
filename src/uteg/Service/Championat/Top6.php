<?php
/**
 * Created by PhpStorm.
 * User: Dania
 * Date: 21.05.2016
 * Time: 11:17
 */

namespace uteg\Service\Championat;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Grade;


class Top6
{
    private $container;
    private $categories;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $em = $this->container->get('doctrine')->getManager();

        $this->categories = array();

        $this->categories[] = $em->getRepository('uteg:Category')->findOneBy(array("id" => 6));
        $this->categories[] = $em->getRepository('uteg:Category')->findOneBy(array("id" => 7));
        $this->categories[] = $em->getRepository('uteg:Category')->findOneBy(array("id" => 8));
    }

    public function championatJudge(\uteg\Entity\Competition $competition, $gender)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $champArr = array();
        $devices = array(1 => 1, 2 => 2, 3 => 3, 4 => 5);

        if ($gender === 'male') {
            $devices[5] = 4;
        }

        //$this->delStarters($competition, $devices, $gender);
        $this->genStarters($competition, $devices, $gender);
        foreach ($devices as $key => $device) {
            $deviceObj = $em->getRepository('uteg:Device')->findOneBy(array('id' => $key));
            $devices[$key] = $deviceObj;
            if (!isset($champArr[$deviceObj->getNumber()])) {
                $champArr[$deviceObj->getNumber()] = array();
                $champArr[$deviceObj->getNumber()]['starters'] = array();
            }
            $champArr[$deviceObj->getNumber()]['starters'] = array_merge($champArr[$deviceObj->getNumber()]['starters'], $this->getCorrectIDs($competition, $this->getStarters($competition, $deviceObj, $gender)));
            $champArr[$deviceObj->getNumber()]['device'] = $deviceObj;
        }

        return $this->container->get('templating')->renderResponse('championat/Top6.html.twig', array(
            "competition" => $competition,
            "starters" => $champArr,
            "devices" => $devices
        ));
    }

    public function championatReport(\uteg\Entity\Competition $competition, $gender, $format)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $champArr = array();
        $devices = array(1 => 1, 2 => 2, 3 => 3, 4 => 5);

        if ($gender === 'male') {
            $devices[5] = 4;
        }

        //$this->delStarters($competition, $devices, $gender);
        $this->genStarters($competition, $devices, $gender);
        foreach ($devices as $key => $device) {
            $deviceObj = $em->getRepository('uteg:Device')->findOneBy(array('id' => $key));
            $devices[$key] = $deviceObj;
            if (!isset($champArr[$deviceObj->getNumber()])) {
                $champArr[$deviceObj->getNumber()] = array();
                $champArr[$deviceObj->getNumber()]['starters'] = array();
            }
            $champArr[$deviceObj->getNumber()]['starters'] = array_merge($champArr[$deviceObj->getNumber()]['starters'], $this->getCorrectIDs($competition, $this->getStarters($competition, $deviceObj, $gender)));
            $champArr[$deviceObj->getNumber()]['device'] = $deviceObj;
        }

        if ($format === "pdf") {
            return $this->renderPdf('Championat', 'championat/reporting/Top6Report.html.twig', array(
                "competition" => $competition,
                "starters" => $champArr
            ));
        }

        return $this->container->get('templating')->renderResponse('championat/reporting/Top6Report.html.twig', array(
            "competition" => $competition,
            "starters" => $champArr
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
            $gradeEntity->setId($competition->getId() . $s2c->getId() . $device->getNumber() . 9);
            $gradeEntity->setS2c($s2c);
            $gradeEntity->setCompetition($competition);
            $gradeEntity->setDevice($device);
            $gradeEntity->setGrade($realGrade);
            $error[$grade['s2c']] = $this->saveGrade($gradeEntity);
        }

        return new Response(json_encode($error));
    }

    public function reportingRanking(\uteg\Entity\Competition $competition, \uteg\Entity\Category $category, $gender, $format)
    {
        $starters = $this->getRankingArray($competition, $category, $gender);
        $starters = $this->sortRanking($starters, $gender);
        $starters = $this->genRanking($starters, 40);
        $highGrades = $this->getHighestGrades($starters, $gender);

        $headers = array(array('name' => 'egt.reporting.ranking.rank', 'style' => 'width: 5px;'),
            array('name' => 'egt.reporting.ranking.firstname', 'style' => ''),
            array('name' => 'egt.reporting.ranking.lastname', 'style' => ''),
            array('name' => 'egt.reporting.ranking.birthyear', 'style' => ''),
            array('name' => 'egt.reporting.ranking.club', 'style' => ''));

        if ($gender === "male") {
            $headers[] = array('name' => 'device.parallel-barsShort', 'style' => 'width: 40px;text-align: center;');
        }

        $headers[] = array('name' => 'device.horizontal-barShort', 'style' => 'width: 40px;text-align: center;');
        $headers[] = array('name' => 'egt.reporting.ranking.total', 'style' => 'width: 40px;text-align: center;');

        if ($format === "pdf") {
            return $this->renderPdf('Ranking', 'championat/reporting/Top6rankingReport.html.twig', array(
                "competition" => $competition,
                "category" => $category,
                "starters" => $starters,
                "headers" => $headers,
                "highGrades" => $highGrades
            ));
        } elseif ($format === "ajax") {
            return $this->container->get('templating')->renderResponse('championat/reporting/Top6rankingReport.html.twig', array(
                "competition" => $competition,
                "category" => $category,
                "starters" => $starters,
                "headers" => $headers,
                "highGrades" => $highGrades
            ));
        }

        $categories = $this->getUsedCategories($competition);

        return $this->container->get('templating')->renderResponse('championat/reporting/Top6ranking.html.twig', array(
            "categories" => $categories,
            "gender" => $gender
        ));
    }

    private function getCorrectIDs(\uteg\Entity\Competition $competition, $starters)
    {
        $em = $this->container->get('Doctrine')->getManager();

        foreach ($starters as $key => $starter) {
            $result = $em
                ->getRepository('uteg:Starters2Competitions')
                ->createQueryBuilder('s')
                ->select('s.id as s2cid, st.id as sid, st.firstname as firstname, st.lastname as lastname, st.birthyear as birthyear, st.gender as gender, cl.name as club, c.name as category')
                ->join('s.starter', 'st', 'WITH', 'st.gender = :gender')
                ->join('s.category', 'c', 'WITH', 's.category = :category')
                ->join('s.club', 'cl')
                ->where('s.present = 1')
                ->andWhere('s.competition = :competition')
                ->andWhere('st.id = :stid')
                ->setParameters(array('gender' => $starter['gender'], 'category' => $em->getRepository('uteg:Category')->find(9), 'competition' => $competition, 'stid' => $starter['sid']))
                ->getQuery()->getResult();

            if ($result) {
                $starters[$key]['s2cid'] = $result[0]['s2cid'];
            }
        }

        return $starters;
    }

    private function getStarters(\uteg\Entity\Competition $competition, \uteg\Entity\Device $device, $gender)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $starters = array();

            $grades = $em
                ->getRepository('uteg:Starters2CompetitionsEGT')
                ->createQueryBuilder('s')
                ->select('g.grade as grade, c.number as category')
                ->join('s.grades', 'g')
                ->join('s.starter', 'st', 'WITH', 'st.gender = :gender')
                ->join('s.category', 'c')
                ->where('s.present = 1')
                ->andWhere('s.competition = :competition')
                ->andWhere('g.device = :device')
                ->andWhere('s.category = :cat1 OR s.category = :cat2 OR s.category = :cat3')
                ->orderBy('g.grade', 'DESC')
                ->setMaxResults(6)
                ->setParameters(array('gender' => $gender, 'competition' => $competition, 'device' => $device, 'cat1' => $this->categories[0], 'cat2' => $this->categories[1], 'cat3' => $this->categories[2]))
                ->getQuery()->getResult();

            foreach ($grades as $grade) {
                $startersCat = $em
                    ->getRepository('uteg:Starters2CompetitionsEGT')
                    ->createQueryBuilder('s')
                    ->select('s.id as s2cid, st.id as sid, st.firstname as firstname, st.lastname as lastname, st.birthyear as birthyear, st.gender as gender, cl.name as club, c.name as category')
                    ->join('s.grades', 'g')
                    ->join('s.starter', 'st', 'WITH', 'st.gender = :gender')
                    ->join('s.category', 'c')
                    ->join('s.club', 'cl')
                    ->where('s.present = 1')
                    ->andWhere('s.competition = :competition')
                    ->andWhere('g.device = :device')
                    ->andWhere('g.grade = :grade')
                    ->andWhere('s.category = :category')
                    ->setParameters(array('gender' => $gender, 'competition' => $competition, 'device' => $device, 'grade' => $grade['grade'], 'category' => $grade['category']))
                    ->getQuery()->getResult();

                foreach ($startersCat as $item) {
                    $starters[] = $item;
                }

        }

        return $starters;
    }

    private function delStarters(\uteg\Entity\Competition $competition, $devices, $gender)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $emS2c = $em->getRepository('uteg:Starters2CompetitionsEGT');
        foreach ($devices as $device) {
            $deviceObj = $em->getRepository('uteg:Device')->findOneBy(array('number' => $device));
            $starters = $this->getCorrectIDs($competition, $this->getStarters($competition, $deviceObj, $gender));
        }

        foreach ($starters as $starter) {
            $s2c = $emS2c->findOneBy(array("id" => $starter['s2cid']));
            $competition->removeS2c($s2c);
            $em->remove($s2c);
        }

        $em->persist($competition);
        $em->flush();
    }

    private function genStarters(\uteg\Entity\Competition $competition, $devices, $gender)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $category = $em->getRepository('uteg:Category')->findOneBy(array('id' => 9));
        $starters = [];

        foreach ($devices as $device) {
            $deviceObj = $em->getRepository('uteg:Device')->findOneBy(array('id' => $device));
            $starters = array_merge($starters, $this->getStarters($competition, $deviceObj, $gender));
        }

        foreach ($starters as $starter) {
            $oldS2c = $em->getRepository('uteg:Starters2Competitions')->findOneBy(array('id' => $starter['s2cid']));
            $starterObj = $em->getRepository('uteg:Starter')->findOneBy(array('id' => $starter['sid']));
            $s2c = $this->container->get($competition->getModule()->getServiceName())->getS2c();
            $s2c->setCompetition($competition);
            $s2c->setCategory($category);
            $s2c->setStarter($starterObj);
            $s2c->setClub($oldS2c->getClub());
            $s2c->setPresent(true);
            $s2c->setMedicalcert(false);
            $starterObj->addS2c($s2c);
            $competition->addS2c($s2c);

            $em->persist($competition);
            $em->persist($s2c);
            $em->persist($starterObj);
        }

        $em->flush();
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

    private function saveGrade(\uteg\Entity\Grade $grade)
    {
        $em = $this->container->get('Doctrine')->getManager();

        $float = explode('.', $grade->getGrade());
        if (!isset($float[1])) {
            $float[1] = 00;
        }

        if ($grade->getGrade() >= 0 && $grade->getGrade() <= 10) {
            $em->merge($grade);
            $em->flush();
            return array('ok');

        } else {
            return array('invalidGrade', $this->container->get('translator')->trans('egt.judging.invalidGrade', array(), 'uteg'));
        }
    }

    private function getRankingArray(\uteg\Entity\Competition $competition, \uteg\Entity\Category $category, $gender)
    {
        $em = $this->container->get('doctrine')->getManager();

        $default = array(1 => number_format((float)"0.00", 2, '.', ''), 2 => number_format((float)"0.00", 2, '.', ''), 3 => number_format((float)"0.00", 2, '.', ''), 5 => number_format((float)"0.00", 2, '.', ''));

        if ($gender === "male") {
            $default[4] = number_format((float)"0.00", 2, '.', '');
        }

        $starters = $em
            ->getRepository('uteg:Starters2CompetitionsEGT')
            ->createQueryBuilder('s2c')
            ->select('s2c.id as s2cid, s.firstname as firstname, s.lastname as lastname, s.birthyear as birthyear, s.gender as gender, c.name as club')
            ->join('s2c.starter', 's')
            ->join('s2c.club', 'c')
            ->join('s2c.category', 'cat')
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
                ->where('g.s2c = :s2c');

            if ($category->getNumber() === 9) {
                $grades->setParameter('s2c', $starter['s2cid']);
            } else {
                $grades->setParameter('s2c', $starter['s2cid']);
            }

            $grades = $grades->getQuery()->getResult();

            $sum = 0;

            foreach ($grades as $grade) {
                $grade['grade'] = number_format((float)$grade['grade'], 2, '.', '');
                $starter[$grade['dnumber']] = $grade['grade'];
                $sum += $grade['grade'];
            }

            $starter = array_replace($default, $starter);
            $starter['total'] = number_format((float)$sum, 2, '.', '');;
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
        $highGrades = array(1 => number_format((float)"0.00", 2, '.', ''), 2 => number_format((float)"0.00", 2, '.', ''), 3 => number_format((float)"0.00", 2, '.', ''), 5 => number_format((float)"0.00", 2, '.', ''));

        if ($gender === "male") {
            $highGrades[4] = number_format((float)"0.00", 2, '.', '');
        }

        foreach ($starters as $starter) {
            (bccomp($highGrades[1], $starter[1], 2) === -1) ? $highGrades[1] = number_format((float)$starter[1], 2, '.', '') : number_format((float)$highGrades[1], 2, '.', '');
            (bccomp($highGrades[2], $starter[2], 2) === -1) ? $highGrades[2] = number_format((float)$starter[2], 2, '.', '') : number_format((float)$highGrades[2], 2, '.', '');
            (bccomp($highGrades[3], $starter[3], 2) === -1) ? $highGrades[3] = number_format((float)$starter[3], 2, '.', '') : number_format((float)$highGrades[3], 2, '.', '');
            if ($gender === "male") {
                (bccomp($highGrades[4], $starter[4], 2) === -1) ? $highGrades[4] = number_format((float)$starter[4], 2, '.', '') : number_format((float)$highGrades[4], 2, '.', '');
            }
            (bccomp($highGrades[5], $starter[5], 2) === -1) ? $highGrades[5] = number_format((float)$starter[5], 2, '.', '') : number_format((float)$highGrades[5], 2, '.', '');
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
            if ($c === 4 && $a['gender'] === "female") {
                $c = 5;
            }

            if ($c === 6) {
                return 0;
            }
        }

        return bccomp($second, $first, 2);
    }
}