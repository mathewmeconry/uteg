<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_club")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Club
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="club_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name")
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Starters2Competitions", mappedBy="club", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $members;

    /**
     * @ORM\OneToMany(targetEntity="Clubs2Invites", mappedBy="club", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $c2is;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->c2is = new ArrayCollection();
    }

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
     * Add c2is
     *
     * @param \uteg\Entity\Clubs2Invites $c2is
     * @return Club
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
