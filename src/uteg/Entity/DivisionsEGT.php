<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DivisionsEGT
 */
class DivisionsEGT
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $starters;

    /**
     * @var \uteg\Entity\Device
     */
    private $device;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->starters = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add starters
     *
     * @param \uteg\Entity\Starters2CompetitionsEGT $starters
     * @return DivisionsEGT
     */
    public function addStarter(\uteg\Entity\Starters2CompetitionsEGT $starters)
    {
        $this->starters[] = $starters;

        return $this;
    }

    /**
     * Remove starters
     *
     * @param \uteg\Entity\Starters2CompetitionsEGT $starters
     */
    public function removeStarter(\uteg\Entity\Starters2CompetitionsEGT $starters)
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
     * Set device
     *
     * @param \uteg\Entity\Device $device
     * @return DivisionsEGT
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
