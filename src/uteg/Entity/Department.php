<?php

namespace uteg\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_department")
 */
class Department
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="dep_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="departments")
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="departments")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id")
     */
    protected $category;

    /**
     * @ORM\Column(type="string", name="gender")
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"male", "female"})
     */
    protected $gender;

    /**
     * @ORM\Column(type="integer", name="number")
     */
    protected $number;

    /**
     * @ORM\Column(type="date", name="date")
     */
    protected $date;

    /**
     * @ORM\Column(type="boolean", name="started", options={"default" = false})
     */
    protected $started = false;

    /**
     * @ORM\Column(type="boolean", name="ended", options={"default" = false})
     */
    protected $ended = false;

    /**
     * @ORM\Column(type="integer", name="round", options={"default" = 0})
     */
    protected $round;

    /**
     * @ORM\OneToMany(targetEntity="Division", mappedBy="department", cascade={"persist", "remove"})
     */
    protected $divisions;

    public function __construct()
    {
        $this->divisions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->number->toString();
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
     * Set number
     *
     * @param integer $number
     * @return Department
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Department
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set started
     *
     * @param boolean $started
     * @return Department
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return boolean
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set ended
     *
     * @param boolean $ended
     * @return Department
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * Get ended
     *
     * @return boolean
     */
    public function getEnded()
    {
        return $this->ended;
    }

    /**
     * Set round
     *
     * @param integer $round
     * @return Department
     */
    public function setRound($round)
    {
        $this->round = $round;

        return $this;
    }

    /**
     * Get round
     *
     * @return integer
     */
    public function getRound()
    {
        return $this->round;
    }

    public function increaseRound()
    {
        $this->round = $this->round + 1;

        return $this;
    }

    /**
     * Add divisions
     *
     * @param \uteg\Entity\Division $division
     * @return Department
     */
    public function addDivision(\uteg\Entity\Division $divisions)
    {
        $this->divisions[] = $divisions;

        return $this;
    }

    /**
     * Remove divisions
     *
     * @param \uteg\Entity\Division $division
     */
    public function removeDivision(\uteg\Entity\Division $divisions)
    {
        $this->divisions->removeElement($divisions);
    }

    /**
     * Get divisions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDivisions()
    {
        return $this->divisions;
    }

    /**
     * Set competition
     *
     * @param \uteg\Entity\Competition $competition
     * @return Department
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
     * Set gender
     *
     * @param string $gender
     * @return Department
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set category
     *
     * @param \uteg\Entity\Category $category
     * @return Department
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
