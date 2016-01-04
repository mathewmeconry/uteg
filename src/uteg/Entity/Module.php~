<?php

namespace uteg\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ute_module")
 */
class Module
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="module_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="displayName")
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", name="serviceName")
     */
    protected $serviceName;

    /**
     * @ORM\OneToMany(targetEntity="Competition", mappedBy="module")
     */
    protected $competitions;

    public function __construct()
    {
        $this->competitions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->displayName;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }
}
