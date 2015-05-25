<?php

namespace AppBundle\Controller;

use AppBundle\ACL\ACLCompetition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\ACL\MaskBuilder;
use AppBundle\Entity\Competition;
use AppBundle\Form\Type\CompetitionType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

class CompetitionController extends Controller
{
    /**
     * @Route("/competitions", name="competitionlist")
     */
    public function comptetitionListAction(Request $request) {
        $this->get('acl_competition')->isGrantedUrl('IS_AUTHENTICATED_FULLY', false);

        $comps = array();

        $user = $this->getUser();
        $authorizationChecker = $this->get('security.authorization_checker');

        foreach($user->getCompetitions() as $comp) {
            $request->getSession()->set('comp', $comp);
            if($authorizationChecker->isGranted('VIEW', $comp)) {
                $comps[] = $comp;
            }
        }

        return $this->render('competitionlist.html.twig', array(
            "comps" => $comps
        ));
    }

    /**
     * @Route("/competition", name="competition")
     */
	public function competitionAction()
	{
		$this->get('acl_competition')->isGrantedUrl('SETTINGS_VIEW');
		
		return $this->render('competition.html.twig');
	}

    /**
     * @Route("/comp/set", name="setCompSession")
     * @Method("POST")
     */
    public function setCompSessionAction(Request $request) {
        $this->get('acl_competition')->isGrantedUrl('IS_AUTHENTICATED_REMEMBERED', false);

        $em = $this->getDoctrine()->getEntityManager();
        $comp = $em->getRepository('AppBundle:Competition')->findOneBy(array('id' => $request->request->get('compid')));

        $request->getSession()->set('comp', $comp);

        $aclcomp = $this->get('acl_competition');
        
        return new Response(
            $aclcomp->getPossibleRoute()
        );
    }
    
    
    /**
     * @Route("/comp/new", name="addNewComp")
     */
    public function addCompAction(Request $request) {
        $aclcomp = $this->get('acl_competition');

        $aclcomp->isGrantedUrl('IS_AUTHENTICATED_REMEMBERED', false);

        $user = $this->getUser();

        $competition = new Competition();

        $form = $this->createForm(new CompetitionType(), $competition);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $competition->setStartdate(new \DateTime($competition->getStartdate()));
            $competition->setEnddate(new \DateTime($competition->getEnddate()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($competition);
            $em->flush();

            $request->getSession()->set('comp', $competition);
            $aclcomp->addPermission(MaskBuilder::MASK_OWNER, array('username' => $user->getUsername()));
            $user->addCompetition($competition);

            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'competitionlist.addcomp.success');

            return new RedirectResponse($this->generateUrl('competitionlist'));
        }

    	return $this->render('addComp.html.twig', array(
    		'form' => $form->createView()
    	));
	}

    /**
     * @Route("/comp/del", name="delComp")
     * @Method("POST")
     */
    public function delCompAction(Request $request) {
        $aclcomp = $this->get('acl_competition');

        $aclcomp->isGrantedUrl('IS_AUTHENTICATED_REMEMBERED', false);

        if(isset($_POST['compid'])) {
            $em = $this->getDoctrine()->getManager();
            $comp = $em->getRepository('AppBundle:Competition')->findOneBy(array("id" => $_POST['compid']));
            $request->getSession()->set('comp', $comp);
            if($aclcomp->isGranted('DELETE')) {
                $em->remove($comp);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'competitionlist.delcomp.success');
                return new Response(
                    'true'
                );
            } else {
                $this->get('session')->getFlashBag()->add('error', 'competitionlist.delcomp.denied');
                return new Response(
                    'false'
                );
            }
        } else {
        	$this->get('session')->getFlashBag()->add('error', 'competitionlist.delcomp.compnotset');
        	return new Response(
        			'false'
        	);
        }
    }
}