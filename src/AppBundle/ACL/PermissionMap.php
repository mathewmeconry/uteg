<?php
namespace AppBundle\ACL;

use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class PermissionMap extends BasicPermissionMap
{
    const PERMISSION_VIEW = 'VIEW';
    const PERMISSION_EDIT = 'EDIT';
    const PERMISSION_CREATE = 'CREATE';
    const PERMISSION_DELETE = 'DELETE';
    const PERMISSION_UNDELETE = 'UNDELETE';
    const PERMISSION_OPERATOR = 'OPERATOR';
    const PERMISSION_DASHBOARD = 'DASHBOARD';
    const PERMISSION_STARTERS_VIEW = 'STARTERS_VIEW';
    const PERMISSION_STARTERS_EDIT = 'STARTERS_EDIT';
    const PERMISSION_SETTINGS_VIEW = 'SETTINGS_VIEW';
    const PERMISSION_SETTINGS_EDIT = 'SETTINGS_EDIT';
    const PERMISSION_PERMISSIONS_VIEW = 'PERMISSIONS_VIEW';
    const PERMISSION_PERMISSIONS_EDIT = 'PERMISSIONS_EDIT';
    const PERMISSION_MASTER = 'MASTER';
    const PERMISSION_OWNER = 'OWNER';

    protected  $map;

    public function __construct()
    {
        $this->map = array(
            self::PERMISSION_VIEW => array(
                MaskBuilder::MASK_VIEW,
                MaskBuilder::MASK_EDIT,
                MaskBuilder::MASK_OPERATOR,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
                MaskBuilder::MASK_DASHBOARD,
                MaskBuilder::MASK_STARTERS_VIEW,
                MaskBuilder::MASK_STARTERS_EDIT,
                MaskBuilder::MASK_SETTINGS_VIEW,
                MaskBuilder::MASK_SETTINGS_EDIT,
                MaskBuilder::MASK_PERMISSIONS_VIEW,
                MaskBuilder::MASK_PERMISSIONS_EDIT,
            ),

            self::PERMISSION_EDIT => array(
                MaskBuilder::MASK_EDIT,
                MaskBuilder::MASK_OPERATOR,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_CREATE => array(
                MaskBuilder::MASK_CREATE,
                MaskBuilder::MASK_OPERATOR,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_DELETE => array(
                MaskBuilder::MASK_DELETE,
                MaskBuilder::MASK_OPERATOR,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_UNDELETE => array(
                MaskBuilder::MASK_UNDELETE,
                MaskBuilder::MASK_OPERATOR,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_OPERATOR => array(
                MaskBuilder::MASK_OPERATOR,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_DASHBOARD => array(
                MaskBuilder::MASK_DASHBOARD,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_STARTERS_EDIT => array(
                MaskBuilder::MASK_STARTERS_EDIT,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_STARTERS_VIEW => array(
                MaskBuilder::MASK_STARTERS_VIEW,
                MaskBuilder::MASK_STARTERS_EDIT,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_SETTINGS_EDIT => array(
                MaskBuilder::MASK_SETTINGS_EDIT,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_SETTINGS_VIEW => array(
                MaskBuilder::MASK_SETTINGS_VIEW,
                MaskBuilder::MASK_SETTINGS_EDIT,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_PERMISSIONS_EDIT => array(
                MaskBuilder::MASK_PERMISSIONS_EDIT,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_PERMISSIONS_VIEW => array(
                MaskBuilder::MASK_PERMISSIONS_VIEW,
                MaskBuilder::MASK_PERMISSIONS_EDIT,
                MaskBuilder::MASK_MASTER,
                MaskBuilder::MASK_OWNER,
            ),

            self::PERMISSION_OWNER => array(
                MaskBuilder::MASK_OWNER,
                MaskBuilder::MASK_MASTER,
            ),

            self::PERMISSION_MASTER => array(
                MaskBuilder::MASK_MASTER,
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getMasks($permission, $object)
    {
        if (!isset($this->map[$permission])) {
            return null;
        }

        return $this->map[$permission];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($permission)
    {
        return isset($this->map[$permission]);
    }
}