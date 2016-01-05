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
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="divisions")
     * @ORM\JoinColumn(name="dev_id", referencedColumnName="dev_id")
     * @Assert\Blank
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
     * Add device
     *
     * @param \uteg\Entity\Device $device
     * @return Divisions
     */
    public function addDevice(\uteg\Entity\Device $device)
    {
        $this->device[] = $device;

        return $this;
    }

    /**
     * Remove device
     *
     * @param \uteg\Entity\Device $device
     */
    public function removeDevice(\uteg\Entity\Device $device)
    {
        $this->device->removeElement($device);
    }

    /**
     * Get device
     *
     * @return \Doctrine\Common\Collections\Collection
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

}
