<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Divisions
 */
class Divisions
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \uteg\Entity\Device
     */
    private $device;


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
     * Set device
     *
     * @param \uteg\Entity\Device $device
     * @return Divisions
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
}
