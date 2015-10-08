<?php

namespace Uteg\EGTBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Uteg\BaseBundle\ACL\ACLCompetition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DivisionController extends Controller
{
    /**
     * @Route("/divisions/squads", name="divSquads")
     */
    public function squadsAction(Request $request)
    {

    }

    /**
     * @Route("/divisions/settings", name="divSettings")
     */
    public function settingsAction(Request $request)
    {

    }
}