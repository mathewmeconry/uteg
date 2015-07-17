<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_club2invites")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Clubs2Invites
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="c2i_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Club", inversedBy="c2is", cascade={"persist"})
     * @ORM\JoinColumn(name="club_id", referencedColumnName="club_id")
     */
    protected $club;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="c2is", cascade={"persist"})
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $token;

    /**
     * @ORM\Column(type="date")
     */
    protected $valid;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    public function __toString()
    {
        return $this->name;
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
     * @return Club
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
     * Set created
     *
     * @param \DateTime $created
     * @return Club
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
     * @param \DateTime $deletedAt
     * @return Club
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add members
     *
     * @param \uteg\Entity\Starters2Competitions $members
     * @return Club
     */
    public function addMember(\uteg\Entity\Starters2Competitions $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \uteg\Entity\Starters2Competitions $members
     */
    public function removeMember(\uteg\Entity\Starters2Competitions $members)
    {
        $this->members->removeElement($members);

        return $this->members;
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set club
     *
     * @param \uteg\Entity\Club $club
     * @return Clubs2Invites
     */
    public function setClub(\uteg\Entity\Club $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \uteg\Entity\Club
     */
    public function getClub()
    {
        return $this->club->getName();
    }

    public function getClubObj()
    {
        return $this->club;
    }

    /**
     * Set competition
     *
     * @param \uteg\Entity\Competition $competition
     * @return Clubs2Invites
     */
    public function setCompetition(\uteg\Entity\Competition $competition = null)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Get competition
     *
     * @return \uteg\Entity\Competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Clubs2Invites
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set valid
     *
     * @param \Date $valid
     * @return Clubs2Invites
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return \Date
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Clubs2Invites
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
