<?php

namespace Uteg\BaseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubsController extends DefaultController
{
    /**
     * @Route("/clubs", name="clubs")
     * @Method("GET")
     */
    public function clubsAction()
    {
        $this->get('acl_competition')->isGrantedUrl('CLUBS_VIEW');
        return $this->render('UtegBaseBundle::clubs.html.twig');
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
            ->from("UtegBaseBundle:Club", "c")
            ->getQuery()
            ->getResult();

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}