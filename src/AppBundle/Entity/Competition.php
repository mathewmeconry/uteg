<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_competition")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Competition {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="comp_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="gym")
     */
    protected $gym;

    /**
     * @ORM\Column(type="string", name="location")
     */
    protected $location;

    /**
     * @ORM\Column(type="integer", name="zipcode", length=4)
     * @Assert\Length(
     *      min = 4,
     *      max = 4
     * )
     */
    protected $zipcode;

    /**
     * @ORM\Column(type="date", name="startdate")
     */
    protected $startdate;

    /**
     * @ORM\Column(type="date", name="enddate")
     */
    protected $enddate;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    /**
     * @ORM\ManytoMany(targetEntity="User", mappedBy="competitions")
     */
    protected $users;

    /**
     * @ORM\OnetoMany(targetEntity="Starters2Competitions", mappedBy="competition", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $s2cs;


    /**
     * Constructor
     */
    public function __construct()
    {
    	$this->users = new ArrayCollection();
    	$this->starters = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Competition
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set gym
     *
     * @param string $gym
     * @return Competition
     */
    public function setGym($gym)
    {
        $this->gym = $gym;

        return $this;
    }

    /**
     * Get gym
     *
     * @return string 
     */
    public function getGym()
    {
        return $this->gym;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Competition
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set zipcode
     *
     * @param integer $zipcode
     * @return Competition
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return integer 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set startdate
     *
     * @param \DateTime $startdate
     * @return Competition
     */
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;

        return $this;
    }

    /**
     * Get startdate
     *
     * @return \DateTime 
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * Set enddate
     *
     * @param \DateTime $enddate
     * @return Competition
     */
    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Get enddate
     *
     * @return \DateTime 
     */
    public function getEnddate()
    {
        return $this->enddate;
    }

    /**
     * Add users
     *
     * @param \AppBundle\Entity\User $users
     * @return Competition
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Competition
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Competition
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set deletedAt
     *
     * @param boolean $deletedAt
     * @return Competition
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return boolean 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add starters
     *
     * @param \AppBundle\Entity\Starters2Competitions $starters
     * @return Competition
     */
    public function addS2c(\AppBundle\Entity\Starters2Competitions $starters)
    {
        $this->s2cs[] = $starters;

        return $this;
    }

    /**
     * Remove starters
     *
     * @param \AppBundle\Entity\Starters2Competitions $starters
     */
    public function removeS2c(\AppBundle\Entity\Starters2Competitions $starters)
    {
        $this->s2cs->removeElement($starters);
    }

    /**
     * Get starters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getS2cs()
    {
        return $this->s2cs;
    }
}
