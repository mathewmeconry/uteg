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
     * @Route("/judging", name="judging")
     * @Method("GET")
     */
    public function judgingAction(Request $request, $compid)
    {

    }
}