<?php

namespace AppBundle\ACL;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ACLCompetition {
    private $em;
    private $aclProvider;
    private $requestStack;
    private $acl;
    private $authorizationChecker;
    private $comp;
    private $isadmin = false;

    public function __construct(EntityManager $em, $aclProvider, RequestStack  $requestStack, $authorizationChecker) {
        $this->em = $em;
        $this->aclProvider = $aclProvider;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function addPermission($permission, $userSearchArray)
    {
        $this->isadmin = ($userSearchArray['username'] == "admin") ? true : false;

        $this->updateAcl();

        $securityIdentity = $this->getUserSecurityIdentityBy($userSearchArray);

        $this->acl->insertObjectAce($securityIdentity, $permission);
        $this->aclProvider->updateAcl($this->acl);
    }

    public function removePermission($permission, $userSearchArray) {
        $this->updateAcl();

        $securityIdentity = $this->getUserSecurityIdentityBy($userSearchArray);
        $ace = $this->acl->getObjectAce($this->getAce($securityIdentity));

        $maskBuilder = new MaskBuilder($ace->getMask());
        $maskBuilder->remove($permission);
        $ace->setMask($maskBuilder->get());

        $this->aclProvider->updateAcl($this->acl);
    }

    public function removeAce($userSearchArray) {
        $this->updateAcl();

        $securityIdentity = $this->getUserSecurityIdentityBy($userSearchArray);
        $this->acl->deleteObjectAce($this->getAce($securityIdentity));

        $this->aclProvider->updateAcl($this->acl);
    }

    public function removeAcl($comp) {
        $objectIdentity = ObjectIdentity::fromDomainObject($comp);
        $this->aclProvider->deleteAcl($objectIdentity);
    }

    public function isGrantedUrl($permission, $acl = true) {
        if($this->isGranted($permission, $acl)) {
            return true;
        } else {
            throw new AccessDeniedException();
        }
    }

    public function isGranted($permission, $acl = true) {
       if($acl) {
           $this->updateAcl();
            if ($this->authorizationChecker->isGranted($permission, $this->comp)) {
                return true;
            } else {
                return false;
            }
        } else {
            if($this->authorizationChecker->isGranted($permission)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getPossibleRoute() {
        $this->updateAcl();

        if($this->isGranted('DASHBOARD')) {
            return "/dashboard";
        } elseif ($this->isGranted('STARTERS_VIEW')){
            return "/starters";
        } elseif ($this->isGranted('SETTINGS_VIEW')){
            return "/competition";
        } elseif ($this->isGranted('PERMISSIONS_VIEW')){
            return "/permissions";
        } else {
            return "/profile/edit";
        }
    }

    private function grantAdmin() {
        $admin = $this->em->getRepository('AppBundle:User')->findOneBy(array('username' => 'admin'));
        $admin->addCompetition($this->comp);
        $this->em->persist($admin);
        $this->em->flush();

        $this->addPermission(MaskBuilder::MASK_MASTER, array('username' => 'admin'));

        $this->isadmin = false;
    }

    private function updateAcl() {
        $this->comp = $this->requestStack->getCurrentRequest()->getSession()->get('comp');
        $objectIdentity = ObjectIdentity::fromDomainObject($this->comp);

        try {
            $this->acl = $this->aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            $this->acl = $this->aclProvider->createAcl($objectIdentity);
            (!$this->isadmin) ? $this->grantAdmin() : "";
        }
    }

    private function getUserSecurityIdentityBy($searchArray) {
        $user = $this->em->getRepository('AppBundle:User')->findOneBy($searchArray);

        return \Symfony\Component\Security\Acl\Domain\UserSecurityIdentity::fromAccount($user);
    }

    private function getAce($securityIdentity) {
        foreach($this->acl->getObjectAces() as $index => $ace) {
            if($ace->getSecurityIdentity()->equals($securityIdentity)) {
                return $index;
                break;
            }
        }
    }
}