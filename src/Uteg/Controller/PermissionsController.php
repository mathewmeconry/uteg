<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PermissionsController extends DefaultController
{
    /**
     * @Route("/permissions", name="permissions")
     */
    public function permissionsAction(Request $request)
    {
        $this->get('acl_competition')->isGrantedUrl('PERMISSIONS_VIEW');

        return $this->render('permissions.html.twig', array(
            "comp" => $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $request->getSession()->get('comp'))
        ));
    }

    /**
     * @Route("/permissions/get", name="permissionsPost")
     * @Method("GET")
     */
    public function permissionsPostAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $acl = $this->get('acl_competition');

        $acl->getPermissionsByComp();
    }
}