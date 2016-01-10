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
     * @ORM\OneToMany(targetEntity="Division", mappedBy="device")
     */
    protected $divisions;

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
}
