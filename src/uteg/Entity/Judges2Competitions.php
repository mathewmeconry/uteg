<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_judges2competitions")
 */
class Judges2Competitions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="j2c_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="j2cs", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="j2cs", cascade={"persist"})
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * @ORM\ManyToOne(targetEntity="Device", cascade={"persist"})
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
     * Set user
     *
     * @param \uteg\Entity\User $user
     * @return Judges2Competitions
     */
    public function setUser(\uteg\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \uteg\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set competition
     *
     * @param \uteg\Entity\Competition $competition
     * @return Judges2Competitions
     */
    public function setCompetition(\uteg\Entity\Competition $competition)
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
     * Set device
     *
     * @param \uteg\Entity\Device $device
     * @return Judges2Competitions
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

    public function getEmail()
    {
        return $this->user->getEmail();
    }
}
