<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 1/4/16
 * Time: 2:55 PM
 */

namespace uteg\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_division")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="module", type="string")
 * @ORM\DiscriminatorMap({"egt" = "DivisionEGT"})
 */
class Division
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="div_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="divisions", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="dep_id", referencedColumnName="dep_id", onDelete="cascade")
     */
    protected $department;

    /**
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="divisions")
     * @ORM\JoinColumn(name="dev_id", referencedColumnName="dev_id")
     */
    protected $device;

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
     * Get device
     *
     * @return \uteg\Entity\Device
     */
    public function getDevice()
    {
        return $this->device;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->device = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set department
     *
     * @param \uteg\Entity\Department $department
     * @return Division
     */
    public function setDepartment(\uteg\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \uteg\Entity\Department 
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set device
     *
     * @param \uteg\Entity\Device $device
     * @return Division
     */
    public function setDevice(\uteg\Entity\Device $device = null)
    {
        $this->device = $device;

        return $this;
    }
}
