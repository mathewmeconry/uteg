<?php

namespace Uteg\EGTBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DivisionController
 * @package Uteg\EGTBundle\Controller
 *
 * @Route("EGT")
 */
class DivisionController extends Controller
{
    /**
     * @Route("/divisions/squads", name="divSquads")
     */
    public function squadsAction(Request $request)
    {
        return $this->render('UtegEGTBundle::divisionsSquad.html.twig');
    }

    /**
     * @Route("/divisions/settings", name="divSettings")
     */
    public function settingsAction(Request $request)
    {

    }
}