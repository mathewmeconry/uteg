<?php

namespace uteg\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
* @ORM\Entity
* @ORM\Table(name="ute_starters2competitions")
* @ORM\InheritanceType("JOINED")
* @ORM\DiscriminatorColumn(name="module", type="string")
* @ORM\DiscriminatorMap({"egt" = "Starters2CompetitionsEGT"})
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
     * @Assert\NotBlank(message="starter.error.club")
     */
    protected $club;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="starters")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id")
     * @Assert\NotBlank(message="starter.error.category")
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
     * @param \uteg\Entity\Starter $starter
     * @return Starters2Competitions
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

    public function getStarterByGender($gender)
    {
        return ($this->starter->getGender() == $gender) ? $this->starter : null;
    }

    public function getStarterByGenderCat($gender, $category)
    {
        return ($this->starter->getGender() == $gender && $this->category->getNumber() == $category) ? $this->starter : null;
    }

    /**
     * Set competition
     *
     * @param \uteg\Entity\Competition $competition
     * @return Starters2Competitions
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
     * @return Starters2Competitions
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
     * @return Starters2Competitions
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

    public function getGender()
    {
        return $this->starter->getGender();
    }

    public function setGender($gender)
    {
        return $this;
    }
}
