<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations

/**
 * @ORM\Entity
 * @ORM\Table(name="ute_starters2competitionsEGT")
 */
 class Starters2CompetitionsEGT extends Starters2Competitions
 {
   /**
    * @ORM\ManyToOne(targetEntity="DivisionEGT", inversedBy="s2cs")
    * @ORM\JoinColumn(name="div_id", referencedColumnName="div_id", onDelete="SET NULL")
    * @Assert\Blank
    */
   protected $division;

    /**
     * Set division
     *
     * @param \uteg\Entity\Divisions $division
     * @return Starters2CompetitionsEGT
     */
    public function setDivision(\uteg\Entity\DivisionEGT $division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division
     *
     * @return \uteg\Entity\Divisions
     */
    public function getDivision()
    {
        return $this->division;
    }
}
