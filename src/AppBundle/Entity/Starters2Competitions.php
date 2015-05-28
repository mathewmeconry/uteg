<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_starters2competitions")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Starters2Competitions {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="s2c_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManytoOne(targetEntity="Starter", inversedBy="competitions")
     * @ORM\JoinColumn(name="starter_id", referencedColumnName="starter_id")
     */
    protected $starter;

    /**
     * @ORM\ManytoOne(targetEntity="Competition", inversedBy="s2cs")
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * @ORM\ManytoOne(targetEntity="Club", inversedBy="members")
     * @ORM\JoinColumn(name="club_id", referencedColumnName="club_id")
     */
    protected $club;

    /**
     * @ORM\ManytoOne(targetEntity="Category", inversedBy="starters")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id")
     */
    protected $category;

    /**
     * @ORM\OnetoMany(targetEntity="Grade", mappedBy="s2c", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $grades;

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

    public function __construct() {
        $this->grades = new ArrayCollection();
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
     * @param \AppBundle\Entity\Starter $starter
     * @return Starters2Competitions
     */
    public function setStarter(\AppBundle\Entity\Starter $starter = null)
    {
        $this->starter = $starter;

        return $this;
    }

    /**
     * Get starter
     *
     * @return \AppBundle\Entity\Starter 
     */
    public function getStarter()
    {
        return $this->starter;
    }

    /**
     * Set competition
     *
     * @param \AppBundle\Entity\Competition $competition
     * @return Starters2Competitions
     */
    public function setCompetition(\AppBundle\Entity\Competition $competition = null)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Get competition
     *
     * @return \AppBundle\Entity\Competition 
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Set club
     *
     * @param \AppBundle\Entity\Club $club
     * @return Starters2Competitions
     */
    public function setClub(\AppBundle\Entity\Club $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \AppBundle\Entity\Club 
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     * @return Starters2Competitions
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add grades
     *
     * @param \AppBundle\Entity\Grade $grades
     * @return Starters2Competitions
     */
    public function addGrade(\AppBundle\Entity\Grade $grades)
    {
        $this->grades[] = $grades;

        return $this;
    }

    /**
     * Remove grades
     *
     * @param \AppBundle\Entity\Grade $grades
     */
    public function removeGrade(\AppBundle\Entity\Grade $grades)
    {
        $this->grades->removeElement($grades);
    }

    /**
     * Get grades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrades()
    {
        return $this->grades;
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
}
