<?php 

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Clubs2Invites;
use uteg\Form\Type\C2iType;

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
					$c2i->setEmail($club['mail']);
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
					
					$this->sendMail($message['default'], $message['valid'], $comp, $club['mail'], $token, (isset($message['message']) ? $message['message'] : null));
	
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
		$result = array();
		
		$comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));
		
		$c2is = $comp->getC2is();
		foreach($c2is as $c2i) {
			$valid = ($c2i->getValid()->format('Y-m-d') < $today) ? 'invites.table.expired' : 'invites.table.valid';
			$result[] = array("DT_RowId" => $c2i->getId(), "name" => $c2i->getClub(), "email" => $c2i->getEmail(), "validUntil" => $c2i->getValid(), "state" => $valid);
		}

		return $this->render('inviteList.json.twig', array(
				'source' => $result
		));
	}

    /**
     * @Route("/invite/edit/{id}", name="inviteEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function inviteEditPostAction(Request $request, $id) {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
        $c2i = $this->getDoctrine()->getManager()->find('uteg:Clubs2Invites', $id);

        $form = $this->createForm(new C2iType(), $c2i);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $c2i = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();

            $em->persist($c2i);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'invites.edit.success');

            return new Response('true');
        }

        return $this->render('form/InviteEdit.html.twig',
            array('form' => $form->createView(),
                'target' => 'inviteEdit'
            )
        );
    }
    
    /**
     * @Route("/invite/resend/{id}", name="inviteResend", defaults={"id": ""}, requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function inviteResendAction(Request $request, $id) {
    	$this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
    	
    	$c2i = $this->getDoctrine()->getManager()->find('uteg:Clubs2Invites', $id);
    	$comp = $this->getDoctrine()->getManager()->find('uteg:Competition', $request->getSession()->get('comp'));
    	
    	$this->sendMail(($request->get('custom') == 'false' ? 'true' : 'false'), $c2i->getValid()->format('Y-m-d'), $comp, $c2i->getEmail(), $c2i->getToken(), $request->get('message'));
    	$this->get('session')->getFlashBag()->add('success', 'invites.resend.success');
    	return new Response("true");
    }

    /**
     * @Route("/invite/remove/{id}", name="inviteRemove", defaults={"id": ""}, requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function inviteRemovePostAction(Request $request, $id) {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
        
        $em = $this->getDoctrine()->getEntityManager();
        $c2i = $em->find('uteg:Clubs2Invites', $id);
        $competition = $em->find('uteg:Competition', $request->getSession()->get('comp'));

        $competition->removeC2i($c2i);
        $em->persist($competition);
        $em->flush();
        return new Response('true');
    }
    
    private function sendMail($default, $validuntil, $comp, $mail, $token, $message = null) {
    	if($default == 'true') {
    		$mailmessage = str_replace('#VALID#', $validuntil, str_replace('#COMPETITION#', $comp->getName(), str_replace("#LINK#", $this->get('router')->generate('inviteToken', array(
    				'token' => $token
    		), true), $this->get('translator')->trans('invite.mail.message', array(), 'uteg'))));
    	} else {
    		$mailmessage = str_replace("#LINK#", $this->get('router')->generate('inviteToken', array(
    				'token' => $token
    		), true), $message);
    	}
    		
    	$mail = \Swift_Message::newInstance()
    	->setSubject($this->get('translator')->trans('invite.mail.subject %compname%', array('%compname%' => $comp->getName()), 'uteg'))
    	->setFrom('noreply@getu.ch')
    	->setTo($mail)
    	->setBody(
    			$mailmessage,
    			'text/html'
    	);
    	
    	$result = $this->get('mailer')->send($mail);
    }
}