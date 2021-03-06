<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Starter;
use uteg\Entity\Club;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="uteg")
     */
    public function rootAction()
    {
        return $this->forward('uteg:Redirecting:checkAuthentication', array("target" => "/competitions"));
    }

    /**
     * @Route("/flashbag", name="parseFlashbag")
     */
    public function flashbagAction()
    {
        return $this->render('parseFlashbag.html.twig');
    }

    /**
     * @Route("/{compid}/show", name="showComp")
     */
    public function showComp(Request $request, $compid) {
        $this->get('acl_competition')->isGrantedUrl('IS_AUTHENTICATED_REMEMBERED', false);

        $em = $this->getDoctrine()->getEntityManager();
        $comp = $em->getRepository('uteg:Competition')->findOneBy(array('id' => $compid));

        $aclcomp = $this->get('acl_competition');

        $request->getSession()->set('comp', $comp->getId());

        return $this->redirectToRoute($aclcomp->getPossibleRoute(), array("compid" => $compid));
    }

    public function autocompleteStartersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $starters = $em->getRepository('uteg:Starter')->findAll();
        $result = array();

        foreach ($starters as $starter) {
            $result[] = array('id' => $starter->getId(),
                "firstname" => $starter->getFirstname(),
                "lastname" => $starter->getLastname(),
                "birthyear" => $starter->getBirthyear(),
                "gender" => $starter->getGender());
        }
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function addMassiveAction($competition, $starters, $club = null)
    {
        $module = $this->get($competition->getModule()->getServiceName());
        $em = $this->getDoctrine()->getManager();
        $return = array();
        $errorMessages = array();
        $fails = array();
        foreach ($starters as $starterPost) {
            $starter = $em->getRepository('uteg:Starter')->findOneBy(array("firstname" => $starterPost['firstname'], "lastname" => $starterPost['lastname'], "birthyear" => $starterPost['birthyear'], "gender" => $starterPost['gender']));
            if (!$starter) {
                $starter = $em->getRepository('uteg:Starter')->findOneBy(array("lastname" => $starterPost['firstname'], "firstname" => $starterPost['lastname'], "birthyear" => $starterPost['birthyear'], "gender" => $starterPost['gender']));
                if (!$starter) {
                    $starter = new Starter();
                    $starter->setStvid($starterPost['stvid']);
                    $starter->setFirstname($starterPost['firstname']);
                    $starter->setLastname($starterPost['lastname']);
                    $starter->setBirthyear($starterPost['birthyear']);
                    $starter->setGender($starterPost['gender']);
                    $s2c = false;
                } else {
                    $s2c = $module->findS2c(array("starter" => $starter, "competition" => $competition));
                }
            } else {
                $s2c = $module->findS2c(array("starter" => $starter, "competition" => $competition));
            }

            if (!$s2c) {
                $s2c = $module->getS2c();
                $s2c->setStarter($starter);
                $s2c->setCompetition($competition);

                $starter->addS2c($s2c);
                $competition->addS2c($s2c);
            }

            if (is_null($club)) {
                $club = $em->getRepository('uteg:Club')->find((array_key_exists('club', $starterPost)) ? $starterPost['club'] : 0);
                if ($club !== 0 && $club) {
                    $s2c->setClub($club);
                } else {
                    $errorMessagesRaw['club'] = 'starter.error.club';
                }
            } else {
                $s2c->setClub($club);
            }

            $category = $em->getRepository('uteg:Category')->find((array_key_exists('category', $starterPost)) ? $starterPost['category'] : 0);
            if ($category !== 0 && $category) {
                $s2c->setCategory($category);
            } else {
                $errorMessagesRaw['category'] = 'starter.error.category';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($starter);
            $errorsS2c = $validator->validate($s2c);
            if (count($errors) <= 0 && count($errorsS2c) <= 0 && $club && $category && $club !== 0 && $category !== 0) {
                $em->persist($starter);
                $em->persist($s2c);
                $em->flush($starter);
                $em->flush($s2c);
            } else {
                $array = $starter->__toArray();
                $array['club']['id'] = (array_key_exists('club', $starterPost)) ? $starterPost['club'] : 0;
                $array['category']['id'] = (array_key_exists('category', $starterPost)) ? $starterPost['category'] : 0;
                foreach ($errors as $error) {
                    $errorMessagesRaw[$error->getPropertyPath()] = $error->getMessage();
                }

                foreach ($errorsS2c as $error) {
                    $errorMessagesRaw[$error->getPropertyPath()] = $error->getMessage();
                }
                $fails[] = $array;
                $errorMessages[] = $errorMessagesRaw;
            }

            $club = null;
            unset($starter);
            unset($s2c);
            unset($errorMessagesRaw);
        }

        if (isset($fails)) {
            $return['fails'] = $fails;
        }

        if (isset($errorMessages)) {
            $return['errorMessages'] = $errorMessages;
        }
        return $return;
    }
}
