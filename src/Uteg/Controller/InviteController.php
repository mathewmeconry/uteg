<?php 

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Clubs2Invites;

class InviteController extends Controller
{
	/**
	 * @Route("/invite", name="invite")
	 * @Method("GET")
	 */
	public function inviteAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
		return $this->render('invite.html.twig');
	}
	
	/**
	 * @Route("/invite", name="invitePost")
	 * @Method("POST")
	 */
	public function invitePostAction(Request $request)
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
		$em = $this->getDoctrine()->getManager();
		$comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));
		$error = false;
		
		$message = $request->request->get('message');
		$clubs = $request->request->get('clubs');
		
		if(is_array($clubs)) {
			try {
				foreach($clubs as $club) {
					$token = sha1(uniqid($club['name'], true));
					$clubobj = $em->find('uteg:Club', $club['id']);
					
					$c2i = $em->find('uteg:Clubs2Invites', $clubobj->getId());
					if(!$c2i) {
						$c2i = new Clubs2Invites();
					}
					$c2i->setClub($clubobj);
					$c2i->setCompetition($comp);
					$c2i->setToken($token);
					$c2i->setValid(new \DateTime($message['valid']));
					
					$clubobj->addC2i($c2i);
					$comp->addC2i($c2i);
					
					$em->persist($c2i);
					$em->persist($clubobj);
					$em->persist($comp);
					$em->flush();
					
					if($message['default'] == 'true') {
						$mailmessage = str_replace('#VALID#', $message['valid'], str_replace('#COMPETITION#', $comp->getName(), str_replace("#LINK#", $this->get('router')->generate('inviteToken', array(
												'token' => $token
										), true), $this->get('translator')->trans('invite.mail.message', array(), 'uteg'))));
					} else {
						$mailmessage = str_replace("#LINK#", $this->get('router')->generate('inviteToken', array(
												'token' => $token
										), true), $message['message']);
					}
					
					$mail = \Swift_Message::newInstance()
					->setSubject($this->get('translator')->trans('invite.mail.subject %compname%', array('%compname%' => $comp->getName()), 'uteg'))
					->setFrom('noreply@getu.ch')
					->setTo($club['mail'])
					->setBody(
							$mailmessage,
							'text/html'
					);
	
					$result = $this->get('mailer')->send($mail);
	
					unset($c2i);
				}
			} catch(Exception $e) {
				$error = true;
			}
		}
		
		if($error) {
			$request->getSession()->getFlashBag()->add('error', 'invites.error');
		} else {
			$request->getSession()->getFlashBag()->add('success', 'invites.success');
		}
		
		return new Response(
				$this->get('router')->generate('inviteList')
		);
	}
	
	/**
	 * @Route("/invite/{token}", name="inviteToken")
	 * @Method("GET")
	 */
	public function inviteTokenAction()
	{
		return $this->render('inviteClubView.html.twig');
	}
	
	/**
	 * @Route("/invites", name="inviteList")
	 * @Method("GET")
	 */
	public function inviteListAction(Request $request)
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');
		return $this->render('inviteList.html.twig', array(
				"comp" => $this->getDoctrine()->getManager()->find('uteg:Competition', $request->getSession()->get('comp'))
		));
	}
	
	/**
	 * @Route("/invites", name="inviteListPost")
	 * @Method("POST")
	 */
	public function inviteListPostAction(Request $request)
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');
		$em = $this->getDoctrine()->getManager();
		$today = date('Y-m-d');
		
		$comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));
		
		$c2is = $comp->getC2is();
		foreach($c2is as $c2i) {
			$valid = ($c2i->getValid()->format('Y-m-d') < $today) ? 'invites.table.expired' : 'invites.table.valid';
			$result[] = array("DT_RowId" => $c2i->getId(), "name" => $c2i->getClub()->getName(), "validUntil" => $c2i->getValid(), "state" => $valid);
		}

		return $this->render('inviteList.json.twig', array(
				'source' => $result
		));
	}
}