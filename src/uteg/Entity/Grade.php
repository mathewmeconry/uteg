<?php

namespace uteg\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_grade")
 */
class Grade
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="grade_id")
     */
    protected $id;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2, name="grade")
     */
    protected $grade;

    /**
     * @ORM\ManyToOne(targetEntity="Starters2CompetitionsEGT", inversedBy="grades")
     * @ORM\JoinColumn(name="s2c_id", referencedColumnName="s2c_id")
     */
    protected $s2c;

    /**
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="grades")
     * @ORM\JoinColumn(name="dev_id", referencedColumnName="dev_id")
     */
    protected $device;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="grades")
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * Set id
     *
     * @param integer $id
     * @return Id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set grade
     *
     * @param string $grade
     * @return Grade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return string 
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set s2c
     *
     * @param \uteg\Entity\Starters2CompetitionsEGT $s2c
     * @return Grade
     */
    public function setS2c(\uteg\Entity\Starters2CompetitionsEGT $s2c = null)
    {
        $this->s2c = $s2c;

        return $this;
    }

    /**
     * Get s2c
     *
     * @return \uteg\Entity\Starters2CompetitionsEGT 
     */
    public function getS2c()
    {
        return $this->s2c;
    }

    /**
     * Set device
     *
     * @param \uteg\Entity\Device $device
     * @return Grade
     */
    public function setDevice(\uteg\Entity\Device $device = null)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return \uteg\Entity\Device 
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set competition
     *
     * @param \uteg\Entity\Competition $competition
     * @return Grade
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
}
