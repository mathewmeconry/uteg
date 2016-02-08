<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations

/**
 * @ORM\Entity
 * @ORM\Table(name="ute_starters2competitionsEGT")
 */
 class Starters2CompetitionsEGT extends Starters2Competitions
 {
   /**
    * @ORM\ManyToOne(targetEntity="DivisionEGT", inversedBy="s2cs")
    * @ORM\JoinColumn(name="div_id", referencedColumnName="div_id", onDelete="SET NULL")
    * @Assert\Blank
    */
   protected $division;

    /**
     * Set division
     *
     * @param \uteg\Entity\Divisions $division
     * @return Starters2CompetitionsEGT
     */
    public function setDivision(\uteg\Entity\DivisionEGT $division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division
     *
     * @return \uteg\Entity\Divisions
     */
    public function getDivision()
    {
        return $this->division;
    }
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $present;

    /**
     * @var boolean
     */
    private $medicalcert;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \uteg\Entity\Starter
     */
    private $starter;

    /**
     * @var \uteg\Entity\Competition
     */
    private $competition;

    /**
     * @var \uteg\Entity\Club
     */
    private $club;

    /**
     * @var \uteg\Entity\Category
     */
    private $category;


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
     * @return Starters2CompetitionsEGT
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
     * Set medicalcert
     *
     * @param boolean $medicalcert
     * @return Starters2CompetitionsEGT
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

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Starters2CompetitionsEGT
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
     * @return Starters2CompetitionsEGT
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
     * @param \uteg\Entity\Starter $starter
     * @return Starters2CompetitionsEGT
     */
    public function setStarter(\uteg\Entity\Starter $starter = null)
    {
        $this->starter = $starter;

        return $this;
    }

    /**
     * Get starter
     *
     * @return \uteg\Entity\Starter 
     */
    public function getStarter()
    {
        return $this->starter;
    }

    /**
     * Set competition
     *
     * @param \uteg\Entity\Competition $competition
     * @return Starters2CompetitionsEGT
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
     * Set club
     *
     * @param \uteg\Entity\Club $club
     * @return Starters2CompetitionsEGT
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
        return $this->club;
    }

    /**
     * Set category
     *
     * @param \uteg\Entity\Category $category
     * @return Starters2CompetitionsEGT
     */
    public function setCategory(\uteg\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \uteg\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
