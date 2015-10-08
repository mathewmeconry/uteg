<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_grade")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Grade {
    /**
     * @ORM\Id
     * @ORM\ManytoOne(targetEntity="Starters2Competitions", inversedBy="grades")
     * @ORM\JoinColumn(name="s2c_id", referencedColumnName="s2c_id")
     */
    protected $s2c;

    /**
     * @ORM\Id
     * @ORM\ManytoOne(targetEntity="Device")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id")
     */
    protected $device;

    /**
     * @ORM\Column(type="decimal", name="grade")
     */
    protected $grade;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    /**
     * Set grade
     *
     * @param \decimal $grade
     * @return Grade
     */
    public function setGrade(\double $grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return \decimal
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Grade
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Grade
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set s2c
     *
     * @param \uteg\Entity\Starters2Competitions $s2c
     * @return Grade
     */
    public function setS2c(\uteg\Entity\Starters2Competitions $s2c)
    {
        $this->s2c = $s2c;

        return $this;
    }

    /**
     * Get s2c
     *
     * @return \uteg\Entity\Starters2Competitions
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
    public function setDevice(\uteg\Entity\Device $device)
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
