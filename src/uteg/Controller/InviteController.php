<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Clubs2Invites;
use uteg\Form\Type\C2iType;

class InviteController extends DefaultController
{
    /**
     * @Route("/{compid}/invite", name="invite")
     * @Method("GET")
     */
    public function inviteAction($compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $this->render('invite.html.twig');
    }

    /**
     * @Route("/{compid}/invite/form", name="inviteForm")
     * @Method("POST")
     */
    public function inviteFormAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
        $em = $this->getDoctrine()->getManager();
        $comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));


        if ($request->request->get('id') !== null) {
            $club = $em->find('uteg:Club', $request->request->get('id'));
            $c2i = $em->getRepository('uteg:Clubs2Invites')->findOneBy(array("club" => $club, "competition" => $comp));
            if(!$c2i) {
                $c2i = new Clubs2Invites();
                $c2i->setClub($club);
                $c2i->setCompetition($comp);
            }
        } else {
            $club = $em->getRepository('uteg:Club')->findOneByName($request->request->get('uteg_invite')['club']);
            $c2i = $em->getRepository('uteg:Clubs2Invites')->findOneBy(array("club" => $club, "competition" => $comp));
            if(!$c2i) {
                $c2i = new Clubs2Invites();
                $c2i->setClub($club);
                $c2i->setCompetition($comp);
            }
        }

        $form = $this->createForm(new C2iType(), $c2i);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $c2i = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $token = sha1(uniqid($c2i->getClub(), true));

            if (!$c2i) {
                $c2i = new Clubs2Invites();
            }

            $c2i->setToken($token);
            $c2i->setValid(new \DateTime('0000-00-00'));

            $club->addC2i($c2i);
            $comp->addC2i($c2i);
            $em->persist($c2i);
            $em->persist($club);
            $em->persist($comp);
            $em->flush();

            return new Response('true');
        }

        return $this->render('form/inviteNewForm.html.twig',
            array('form' => $form->createView(),
                'target' => 'inviteForm'
            )
        );
    }

    /**
     * @Route("/{compid}/invite", name="invitePost")
     * @Method("POST")
     */
    public function invitePostAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');
        $em = $this->getDoctrine()->getManager();
        $comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));
        $error = false;

        $message = $request->request->get('message');
        $clubs = $request->request->get('clubs');

        if (is_array($clubs)) {
            try {
                foreach ($clubs as $club) {
                    $clubobj = $em->find('uteg:Club', $club['id']);
                    $c2i = $em->getRepository('uteg:Clubs2Invites')->findOneBy(array("club" => $clubobj, "competition" => $em->find('uteg:Competition', $request->getSession()->get('comp'))));
                    $c2i->setValid(new \DateTime($message['valid']));
                    $em->persist($c2i);
                    $em->flush();

                    $token = $c2i->getToken();
                    $this->sendMail($message['default'], $comp, $c2i, (isset($message['message']) ? $message['message'] : null));

                    unset($c2i);
                }
            } catch (Exception $e) {
                $error = true;
            }
        }

        if ($error) {
            $request->getSession()->getFlashBag()->add('error', 'invites.error');
        } else {
            $request->getSession()->getFlashBag()->add('success', 'invites.success');
        }

        return new Response(
            $this->get('router')->generate('inviteList', array('compid' => $compid))
        );
    }

    /**
     * @Route("/{compid}/invite/club/{token}", name="inviteToken")
     * @Method("GET")
     */
    public function inviteTokenAction($token, $compid)
    {
        $em = $this->getDoctrine()->getManager();
        $c2i = $em->getRepository('uteg:Clubs2Invites')->findOneBy(array("token" => $token));
        $today = date('Y-m-d');

        if ($c2i->getValid()->format('Y-m-d') >= $today) {
            $categories = $em->getRepository('uteg:Category')->findBy(array(), array('number' => 'asc'));
            $qb = $em->createQueryBuilder();
            $qb->select('s2c')
                ->distinct()
                ->from('uteg\Entity\Starters2Competitions', 's2c')
                ->join('uteg\Entity\Competition', 'co', \Doctrine\ORM\Query\Expr\Join::WITH, 's2c.competition = co.id')
                ->where('s2c.club = ?1')
                ->andWhere('co.id = (SELECT MAX(s2c2.competition) FROM uteg\Entity\Starters2Competitions as s2c2 WHERE s2c2.club = ?2 AND s2c2.competition < ?3)')
                ->setParameter(1, $c2i->getClubObj())
                ->setParameter(2, $c2i->getClubObj())
                ->setParameter(3, $c2i->getCompetition());

            return $this->render('inviteClubView.html.twig',
                array('categories' => $categories,
                    'starters' => $qb->getQuery()->getResult()
                )
            );
        } else {
            return new Response('Link expired');
        }
    }

    /**
     * @Route("/{compid}/invite/club/add/{token}", name="inviteAddToken")
     */
    public function inviteAddTokenAction(Request $request, $token, $compid)
    {
        $em = $this->getDoctrine()->getManager();
        $c2i = $em->getRepository('uteg:Clubs2Invites')->findOneBy(array("token" => $token));
        $today = date("Y-m-d");

        if ($c2i->getValid()->format('Y-m-d') >= $today) {
            $starters = $request->request->get('data');

            if (isset($starters[0])) {
                $starters = array_pop($starters);
                $categories = $em->getRepository('uteg:Category')->findBy(array(), array('number' => 'asc'));

                $return = $this->addMassiveAction($em->find('uteg:Competition', $request->getSession()->get('comp')), $request->request->get('data'), $c2i->getClubObj());

                if (isset($return['fails'])) {
                    return $this->render('inviteClubView.html.twig',
                        array('categories' => $categories,
                            'starters' => $return['fails'],
                            'errors' => $return['errorMessages']
                        )
                    );
                } else {
                    return $this->render('inviteClubSuccess.html.twig');
                }
            } else {
                return $this->redirectToRoute("inviteToken", array("token" => $token, "compid" => $compid));
            }
        } else {
            return new Response('Link expired');
        }
    }

    /**
     * @Route("/{compid}/invites", name="inviteList")
     * @Method("GET")
     */
    public function inviteListAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $this->render('inviteList.html.twig', array(
            "comp" => $comp
        ));
    }

    /**
     * @Route("/{compid}/invites", name="inviteListPost")
     * @Method("POST")
     */
    public function inviteListPostAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');
        $em = $this->getDoctrine()->getManager();
        $today = date('Y-m-d');
        $result = array();

        $comp = $em->find('uteg:Competition', $request->getSession()->get('comp'));

        $c2is = $comp->getC2is();
        foreach ($c2is as $c2i) {
            $valid = ($c2i->getValid()->format('Y-m-d') < $today) ? 'invites.table.expired' : 'invites.table.valid';
            $result[] = array("DT_RowId" => $c2i->getId(), "name" => $c2i->getClub(), "firstname" => $c2i->getFirstname(), "lastname" => $c2i->getLastname(), "email" => $c2i->getEmail(), "validUntil" => $c2i->getValid(), "state" => $valid);
        }

        return $this->render('inviteList.json.twig', array(
            'source' => $result
        ));
    }

    /**
     * @Route("/{compid}/invite/edit/{id}", name="inviteEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function inviteEditPostAction(Request $request, $id, $compid)
    {
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

        return $this->render('form/inviteEditForm.html.twig',
            array('form' => $form->createView(),
                'target' => 'inviteEdit'
            )
        );
    }

    /**
     * @Route("/{compid}/invite/resend/{id}", name="inviteResend", defaults={"id": ""}, requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function inviteResendAction(Request $request, $id, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');

        $c2i = $this->getDoctrine()->getManager()->find('uteg:Clubs2Invites', $id);
        $comp = $this->getDoctrine()->getManager()->find('uteg:Competition', $request->getSession()->get('comp'));

        $this->sendMail(($request->get('custom') == 'false' ? 'true' : 'false'), $comp, $c2i, $request->get('message'));
        $this->get('session')->getFlashBag()->add('success', 'invites.resend.success');
        return new Response("true");
    }

    /**
     * @Route("/{compid}/invite/remove/{id}", name="inviteRemove", defaults={"id": ""}, requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function inviteRemovePostAction(Request $request, $id, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');

        $em = $this->getDoctrine()->getEntityManager();
        $c2i = $em->find('uteg:Clubs2Invites', $id);
        $competition = $em->find('uteg:Competition', $request->getSession()->get('comp'));

        $competition->removeC2i($c2i);
        $em->persist($competition);
        $em->flush();
        return new Response('true');
    }

    private function sendMail($default, $comp, $c2i, $message = null)
    {
        if ($default == 'true') {
            $mailmessage = str_replace("#FIRSTNAME#", $c2i->getFirstname(), str_replace("#LASTNAME#", $c2i->getLastname(), str_replace('#VALID#', $c2i->getValid()->format('Y-m-d'), str_replace('#COMPETITION#', $comp->getName(), str_replace("#INVITELINK#", $this->get('router')->generate('inviteToken', array(
                'token' => $c2i->getToken(),
                'compid' => $comp->getId()
            ), true), $this->get('translator')->trans('invite.mail.message', array(), 'uteg'))))));
        } else {
            $mailmessage = str_replace("#FIRSTNAME#", $c2i->getFirstname(), str_replace("#LASTNAME#", $c2i->getLastname(), str_replace("#INVITELINK#", $this->get('router')->generate('inviteToken', array(
                'token' => $c2i->getToken(),
                'compid' => $comp->getId()
            ), true), $message)));
        }

        $mail = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('invite.mail.subject %compname%', array('%compname%' => $comp->getName()), 'uteg'))
            ->setFrom('noreply@getu.ch')
            ->setTo($c2i->getEMail())
            ->setBody(
                $mailmessage,
                'text/html'
            );

        $result = $this->get('mailer')->send($mail);
    }
}