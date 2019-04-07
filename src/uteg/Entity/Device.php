<?php

namespace uteg\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_device")
 */
class Device
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="dev_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", name="number")
     */
    protected $number;

    /**
     * @ORM\Column(type="string", name="name")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="nameShort")
     */
    protected $nameShort;

    /**
     * @ORM\OneToMany(targetEntity="Division", mappedBy="device")
     */
    protected $divisions;

    /**
     * @ORM\OneToMany(targetEntity="Grade", mappedBy="device")
     */
    protected $grades;

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
     * @return Device
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Device
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
     * Set name
     *
     * @param string $name
     * @return Device
     */
    public function setNameShort($name)
    {
        $this->nameShort = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getNameShort()
    {
        return $this->nameShort;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->divisions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add divisions
     *
     * @param \uteg\Entity\Division $divisions
     * @return Device
     */
    public function addDivision(\uteg\Entity\Division $divisions)
    {
        $this->divisions[] = $divisions;

        return $this;
    }

    /**
     * Remove divisions
     *
     * @param \uteg\Entity\Division $divisions
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
     * Add grade
     *
     * @param \uteg\Entity\Grade $grade
     * @return Device
     */
    public function addGrade(\uteg\Entity\Grade $grade)
    {
        $this->grade[] = $grade;

        return $this;
    }

    /**
     * Remove grade
     *
     * @param \uteg\Entity\Grade $grade
     */
    public function removeGrade(\uteg\Entity\Grade $grade)
    {
        $this->grade->removeElement($grade);
    }

    /**
     * Get grade
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrade()
    {
        return $this->grade;
    }
}
