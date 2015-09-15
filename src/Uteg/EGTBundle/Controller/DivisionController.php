<?php

namespace Uteg\EGTBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class DivisionController
 * @package Uteg\EGTBundle\Controller
 *
 * @Route("EGT")
 */
class DivisionController extends Controller
{
    /**
     * @Route("/divisions/squads", name="divSquad")
     */
    public function squadsAction(Request $request)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('UtegBaseBundle:Competition', $request->getSession()->get('comp'));
        $s2cs = $comp->getS2cs();
        $starters  = array();
        $clubs  = array();

        foreach($s2cs as $s2c)
        {
            $starter = $s2c->getStarter();
            if(!in_array($s2c->getClub()->getId(), array_column($clubs, 'id')))
            {
                $clubs[] = array('id' => $s2c->getClub()->getId(),
                    'name' => $s2c->getClub()->getName(),
                    'starters' => array('male' => array(1  => array(),
                            2  => array(),
                            3  => array(),
                            4  => array(),
                            5  => array(),
                            6  => array(),
                            7  => array(),
                            8  => array()
                        ),
                        'female' => array(1  => array(),
                            2  => array(),
                            3  => array(),
                            4  => array(),
                            5  => array(),
                            6  => array(),
                            7  => array(),
                            8  => array()
                        )),
                    'catCount' => array('male' => array(1  => 0,
                            2  => 0,
                            3  => 0,
                            4  => 0,
                            5  => 0,
                            6  => 0,
                            7  => 0,
                            8  => 0
                        ),
                        'female' => array(1  => 0,
                            2  => 0,
                            3  => 0,
                            4  => 0,
                            5  => 0,
                            6  => 0,
                            7  => 0,
                            8  => 0
                        )
                    )
                );
            }

            $clubs[array_search($s2c->getClub()->getId(), array_column($clubs, 'id'))]['catCount'][$starter->getSex()][$s2c->getCategory()->getNumber()] += 1;
            $clubs[array_search($s2c->getClub()->getId(), array_column($clubs, 'id'))]['starters'][$starter->getSex()][$s2c->getCategory()->getNumber()][] = array('id' => $starter->getId(),
                'firstname' => $starter->getFirstname(),
                'lastname' => $starter->getLastname(),
                'sex' => $starter->getSex(),
                'club'  => $s2c->getClub()->getId(),
                'cat'  => $s2c->getCategory()->getNumber(),
            );
        }

        return $this->render('UtegEGTBundle::divisionsSquad.html.twig', array(
            'starters' => $starters,
            'clubs' => $clubs
        ));
    }

    /**
     * @Route("/divisions/settings", name="divSettings")
     */
    public function settingsAction(Request $request)
    {

    }
}