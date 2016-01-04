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
 * @ORM\Table(name="ute_divisions")
 */
class Divisions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="div_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Device", mappedBy="divisions")
     */
    protected $device;

    /**
     * @ORM\OneToMany(targetEntity="Starters2Competition", mappedBy="division")
     */
    protected $starters;

    public function __construct()
    {
      $this->starters = new ArrayCollection();
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
     * Add starters
     *
     * @param \uteg\Entity\Starters2Competition $starters
     * @return Divisions
     */
    public function addStarter(\uteg\Entity\Starters2Competition $starters)
    {
        $this->starters[] = $starters;

        return $this;
    }

    /**
     * Remove starters
     *
     * @param \uteg\Entity\Starters2Competition $starters
     */
    public function removeStarter(\uteg\Entity\Starters2Competition $starters)
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
}
