<?php

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PermissionsController extends DefaultController
{
    /**
     * @Route("/{compid}/permissions", name="permissions")
     * @Method("GET")
     */
    public function permissionsAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('PERMISSIONS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        return $this->render('permissions.html.twig', array(
            "comp" => $comp
        ));
    }

    /**
     * @Route("/{compid}/permissions", name="permissionsPost")
     * @Method("POST")
     */
    public function permissionsPostAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('PERMISSIONS_VIEW');

        $acl = $this->get('acl_competition');

        $users = $acl->getPermissionsByComp();
        $return = array();
        foreach ($users as $user) {
            if($user['username'] != 'admin') {
                $userOptions['username'] = $user['username'];
                $userOptions['email'] = $user['email'];

                $userOptions['dashboard'] = 'fa-lock text-danger';
                $userOptions['starters'] = 'fa-lock text-danger';
                $userOptions['clubs'] = 'fa-lock text-danger';
                $userOptions['settings'] = 'fa-lock text-danger';
                $userOptions['permissions'] = 'fa-lock text-danger';
                $userOptions['owner'] = 'fa-lock text-danger';

                foreach ($user['permissions'] as $key => $permission) {
                    switch ($key) {
                        case 'dashboard':
                            $userOptions['dashboard'] = 'fa-unlock text-success';
                            break;
                        case 'starters_view':
                            $userOptions['starters'] = 'fa-eye text-success';
                            break;
                        case 'starters_edit':
                            $userOptions['starters'] = 'fa-pencil text-warning';
                            break;
                        case 'clubs_view':
                            $userOptions['clubs'] = 'fa-eye text-success';
                            break;
                        case 'clubs_edit':
                            $userOptions['clubs'] = 'fa-pencil text-warning';
                            break;
                        case 'settings_view':
                            $userOptions['settings'] = 'fa-eye text-success';
                            break;
                        case 'settings_edit':
                            $userOptions['settings'] = 'fa-pencil text-warning';
                            break;
                        case 'permissions_view':
                            $userOptions['permissions'] = 'fa-eye text-success';
                            break;
                        case 'permissions_edit':
                            $userOptions['permissions'] = 'fa-pencil text-warning';
                            break;
                        case 'owner':
                            $userOptions['dashboard'] = 'fa-unlock text-success';
                            $userOptions['starters'] = 'fa-pencil text-warning';
                            $userOptions['clubs'] = 'fa-pencil text-warning';
                            $userOptions['settings'] = 'fa-pencil text-warning';
                            $userOptions['permissions'] = 'fa-pencil text-warning';
                            $userOptions['owner'] = 'fa-unlock text-success';
                            break;
                    }
                }

                $return[] = $userOptions;
                unset($userOptions);
            }
        }

        return new Response(json_encode(array("data" => $return)));
    }
}