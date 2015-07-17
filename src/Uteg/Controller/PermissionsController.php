<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PermissionsController extends DefaultController
{
    /**
     * @Route("/permissions", name="permissions")
     */
    public function permissionsAction()
    {
        return $this->render('permissions.html.twig');
    }
}