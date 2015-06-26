<?php 

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Clubs2Invites;

class ClubsController extends Controller
{
	/**
	 * @Route("/clubs", name="clubs")
	 * @Method("GET")
	 */
	public function clubsAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');
		return $this->render('clubs.html.twig');
	}
	
	/**
	 * @Route("/clubs/invite", name="clubsInvite")
	 * @Method("GET")
	 */
	public function clubsInviteAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
		return $this->render('clubsInvite.html.twig');
	}
	
	/**
	 * @Route("/clubs/invite", name="clubsInvitePost")
	 * @Method("POST")
	 */
	public function clubsInvitePostAction(Request $request)
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
		$em = $this->getDoctrine()->getManager();
		$comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));
		$responseUrl = $this->get('router')->generate('clubsInviteSuccess');
		
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
						$mailmessage = str_replace('#VALID#', $message['valid'], str_replace('#COMPETITION#', $comp->getName(), str_replace("#LINK#", $this->get('router')->generate('clubsInviteToken', array(
												'token' => $token
										), true), $this->get('translator')->trans('clubs.invite.mail.message', array(), 'uteg'))));
					} else {
						$mailmessage = str_replace("#LINK#", $this->get('router')->generate('clubsInviteToken', array(
												'token' => $token
										), true), $message['message']);
					}
					
					$mail = \Swift_Message::newInstance()
					->setSubject($this->get('translator')->trans('clubs.invite.mail.subject %compname%', array('%compname%' => $comp->getName()), 'uteg'))
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
				$responseUrl = $this->get('router')->generate('clubsInviteFailed');
			}
		}
		
		return new Response(
				$responseUrl
		);
	}
	
	/**
	 * @Route("/clubs/invite/{token}", name="clubsInviteToken")
	 * @Method("GET")
	 */
	public function clubsInviteTokenAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
		return $this->render('clubsInviteSuccess.html.twig');
	}
	
	/**
	 * @Route("/clubs/invite/success", name="clubsInviteSuccess")
	 * @Method("GET")
	 */
	public function clubsInviteSuccessAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');	
		return $this->render('clubsInviteSuccess.html.twig');
	}
	
	/**
	 * @Route("/clubs/invite/failed", name="clubsInviteFailed")
	 * @Method("GET")
	 */
	public function clubsInviteFailedAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
		return $this->render('clubsInviteFailed.html.twig');
	}
	
	/**
	 * @Route("/clubs", name="clubsPost")
	 * @Method("POST")
	 */
	public function clubsPostAction()
	{
		$this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');

		$qb = $this->getDoctrine()->getManager()->createQueryBuilder();
		$result['data'] = $qb->select("c.id as DT_RowId, c.name as name")
					->from("uteg:Club" ,"c")
					->getQuery()
					->getResult();

		$response = new Response(json_encode($result));
		$response->headers->set('Content-Type', 'application/json');
			
		return $response;
	}
}