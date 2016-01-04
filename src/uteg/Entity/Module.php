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

    /**
     * Set displayName
     *
     * @param string $displayName
     * @return Module
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Set serviceName
     *
     * @param string $serviceName
     * @return Module
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    /**
     * Add competitions
     *
     * @param \uteg\Entity\Competition $competitions
     * @return Module
     */
    public function addCompetition(\uteg\Entity\Competition $competitions)
    {
        $this->competitions[] = $competitions;

        return $this;
    }

    /**
     * Remove competitions
     *
     * @param \uteg\Entity\Competition $competitions
     */
    public function removeCompetition(\uteg\Entity\Competition $competitions)
    {
        $this->competitions->removeElement($competitions);
    }

    /**
     * Get competitions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompetitions()
    {
        return $this->competitions;
    }
}
