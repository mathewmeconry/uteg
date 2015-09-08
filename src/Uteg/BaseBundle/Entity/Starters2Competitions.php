<?php

namespace Uteg\BaseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_starters2competitions")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Starters2Competitions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="s2c_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Starter", inversedBy="s2cs", cascade={"persist"})
     * @ORM\JoinColumn(name="starter_id", referencedColumnName="starter_id")
     */
    protected $starter;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="s2cs")
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * @ORM\ManyToOne(targetEntity="Club", inversedBy="members")
     * @ORM\JoinColumn(name="club_id", referencedColumnName="club_id")
     * @Assert\NotBlank(message="stater.error.club")
     */
    protected $club;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="starters")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id")
     * @Assert\NotBlank(message="stater.error.category")
     */
    protected $category;

    /**
     * @ORM\Column(type="boolean", name="present", options={"default" = 0})
     */
    protected $present;

    /**
     * @ORM\Column(type="boolean", name="medicalcert", options={"default" = 0})
     */
    protected $medicalcert;

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
        $this->starter = new Starter();
        $this->present = 0;
        $this->medicalcert = 0;
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
     * Set present
     *
     * @param boolean $present
     * @return Starters2Competitions
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }

    /**
     * Get present
     *
     * @return boolean
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Starters2Competitions
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
     * @return Starters2Competitions
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
     * Set starter
     *
     * @param \Uteg\BaseBundle\Entity\Starter $starter
     * @return Starters2Competitions
     */
    public function setStarter(\Uteg\BaseBundle\Entity\Starter $starter = null)
    {
        $this->starter = $starter;

        return $this;
    }

    /**
     * Get starter
     *
     * @return \Uteg\BaseBundle\Entity\Starter
     */
    public function getStarter()
    {
        return $this->starter;
    }

    public function getStarterBySex($sex)
    {
        return ($this->starter->getSex() == $sex) ? $this->starter : null;
    }

    public function getStarterBySexCat($sex, $category)
    {
        return ($this->starter->getSex() == $sex && $this->category->getNumber() == $category) ? $this->starter : null;
    }

    /**
     * Set competition
     *
     * @param \Uteg\BaseBundle\Entity\Competition $competition
     * @return Starters2Competitions
     */
    public function setCompetition(\Uteg\BaseBundle\Entity\Competition $competition = null)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Get competition
     *
     * @return \Uteg\BaseBundle\Entity\Competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Set club
     *
     * @param \Uteg\BaseBundle\Entity\Club $club
     * @return Starters2Competitions
     */
    public function setClub(\Uteg\BaseBundle\Entity\Club $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Uteg\BaseBundle\Entity\Club
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set category
     *
     * @param \Uteg\BaseBundle\Entity\Category $category
     * @return Starters2Competitions
     */
    public function setCategory(\Uteg\BaseBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Uteg\BaseBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set medicalcert
     *
     * @param boolean $medicalcert
     * @return Starters2Competitions
     */
    public function setMedicalcert($medicalcert)
    {
        $this->medicalcert = $medicalcert;

        return $this;
    }

    /**
     * Get medicalcert
     *
     * @return boolean
     */
    public function getMedicalcert()
    {
        return $this->medicalcert;
    }

    public function getFirstname()
    {
        return $this->starter->getFirstname();
    }

    public function setFirstname($firstname)
    {
        return $this;
    }

    public function getLastname()
    {
        return $this->starter->getLastname();
    }

    public function setLastname($lastname)
    {
        return $this;
    }

    public function getBirthyear()
    {
        return $this->starter->getBirthyear();
    }

    public function setBirthyear($birthyear)
    {
        return $this;
    }

    public function getSex()
    {
        return $this->starter->getSex();
    }

    public function setSex($sex)
    {
        return $this;
    }
}