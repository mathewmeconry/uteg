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
     * @ORM\Column(type="integer", name="number")
     */
    protected $number;

    /**
     * @ORM\Column(type="date", name="date")
     */
    protected $date;

    /**
     * @ORM\Column(type="boolean", name="started", options={"default" = 0})
     */
    protected $started;

    /**
     * @ORM\Column(type="boolean", name="ended", options={"default" = 0})
     */
    protected $ended;

    /**
     * @ORM\Column(type="integer", name="round", options={"default" = 0})
     */
    protected $round;

    /**
     * @ORM\OneToMany(targetEntity="Divisions", mappedBy="department")
     */
    protected $divisions;

    public function __construct()
    {
        $this->starters = new ArrayCollection();
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

    /**
     * Add starters
     *
     * @param \uteg\Entity\Starters2Competitions $starters
     * @return Department
     */
    public function addStarter(\uteg\Entity\Starters2Competitions $starters)
    {
        $this->starters[] = $starters;

        return $this;
    }

    /**
     * Remove starters
     *
     * @param \uteg\Entity\Starters2Competitions $starters
     */
    public function removeStarter(\uteg\Entity\Starters2Competitions $starters)
    {
        $this->starters->removeElement($starters);
    }

    /**
     * Get starters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStarters()
    {
        return $this->starters;
    }

    /**
     * Add divisions
     *
     * @param \uteg\Entity\Divisions $divisions
     * @return Department
     */
    public function addDivision(\uteg\Entity\Divisions $divisions)
    {
        $this->divisions[] = $divisions;

        return $this;
    }

    /**
     * Remove divisions
     *
     * @param \uteg\Entity\Divisions $divisions
     */
    public function removeDivision(\uteg\Entity\Divisions $divisions)
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
}
