<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Doctrine\Common\Collections\Criteria;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_competition")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Competition
{
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
     * @ORM\ManyToMany(targetEntity="User", mappedBy="competitions")
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="Starters2Competitions", mappedBy="competition", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $s2cs;

    /**
     * @ORM\OneToMany(targetEntity="Clubs2Invites", mappedBy="competition", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $c2is;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->s2cs = new ArrayCollection();
        $this->c2is = new ArrayCollection();
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
     * @param \uteg\Entity\User $users
     * @return Competition
     */
    public function addUser(\uteg\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \uteg\Entity\User $users
     */
    public function removeUser(\uteg\Entity\User $users)
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
     * Add s2cs
     *
     * @param \uteg\Entity\Starters2Competitions $starters
     * @return Competition
     */
    public function addS2c(\uteg\Entity\Starters2Competitions $starters)
    {
        $this->s2cs[] = $starters;

        return $this;
    }

    /**
     * Remove s2cs
     *
     * @param \uteg\Entity\Starters2Competitions $starters
     */
    public function removeS2c(\uteg\Entity\Starters2Competitions $starters)
    {
        $this->s2cs->removeElement($starters);
    }

    /**
     * Get s2cs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getS2cs()
    {
        return $this->s2cs;
    }

    public function getS2csBySex($sex)
    {
        $return = array();

        foreach ($this->s2cs as $s2c) {
            if ($s2c->getStarterBySex($sex) !== null) {
                $return[] = $s2c;
            }
        }

        return $return;
    }

    public function getS2csBySexCat($sex, $category)
    {
        $return = array();

        foreach ($this->s2cs as $s2c) {
            if ($s2c->getStarterBySexCat($sex, $category) !== null) {
                $return[] = $s2c;
            }
        }

        return $return;
    }

    /**
     * Add c2is
     *
     * @param \uteg\Entity\Clubs2Invites $c2is
     * @return Competition
     */
    public function addC2i(\uteg\Entity\Clubs2Invites $c2is)
    {
        $this->c2is[] = $c2is;

        return $this;
    }

    /**
     * Remove c2is
     *
     * @param \uteg\Entity\Clubs2Invites $c2is
     */
    public function removeC2i(\uteg\Entity\Clubs2Invites $c2is)
    {
        $this->c2is->removeElement($c2is);
    }

    /**
     * Get c2is
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getC2is()
    {
        return $this->c2is;
    }
}