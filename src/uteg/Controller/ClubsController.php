<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use uteg\Entity\Club;
use uteg\Form\Type\ClubType;

class ClubsController extends DefaultController
{
    /**
     * @Route("{compid}/clubs", name="clubs")
     * @Method("GET")
     */
    public function clubsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');
        return $this->render('clubs.html.twig', array(
            "comp" => $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $request->getSession()->get('comp'))
        ));
    }

    /**
     * @Route("/{compid}/club/add", name="clubAdd")
     * @Method("POST")
     */
    public function clubAddAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_EDIT');

        $form = $this->createForm(new clubType(false));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $formdata = $form->getData();

            $club = $em->getRepository('uteg:Club')->findOneBy(array("name" => $formdata->getName()));
            if (!$club) {
                $club = new Club();
                $club->setName($formdata->getName());
            }

            $em->persist($club);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'clubs.add.success');

            return new Response('true');
        }

        return $this->render('form/clubAdd.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{compid}/clubs", name="clubsPost")
     * @Method("POST")
     */
    public function clubsPostAction($compid)
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $result['data'] = $qb->select("c.id as DT_RowId, c.name as name")
            ->from("uteg:Club", "c")
            ->getQuery()
            ->getResult();

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}