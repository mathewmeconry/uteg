<?php

namespace uteg\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations

/**
 * @ORM\Entity
 */
 class DivisionEGT extends Division
 {
  /**
   * @ORM\OneToMany(targetEntity="Starters2CompetitionsEGT", mappedBy="division")
   */
  protected $s2cs;

  public function __construct()
  {
    $this->s2cs = new ArrayCollection();
  }
    /**
     * Add starters
     *
     * @param \uteg\Entity\Starters2CompetitionsEGT $starters
     * @return DivisionsEGT
     */
    public function addS2c(\uteg\Entity\Starters2CompetitionsEGT $s2c)
    {
        $this->s2cs[] = $s2c;

        return $this;
    }

    /**
     * Remove starters
     *
     * @param \uteg\Entity\Starters2CompetitionsEGT $starters
     */
    public function removeS2c(\uteg\Entity\Starters2CompetitionsEGT $s2c)
    {
        $this->s2cs->removeElement($s2c);
    }

    /**
     * Get starters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getS2cs()
    {
        return $this->s2cs;
    }

     public function getS2csByClub(\uteg\Entity\Club $club) {
         $return = [];

         foreach ($this->s2cs as $s2c) {
             if($s2c->getClub === $club) {
                $return[] = $s2c;
             }
         }

         return $return;
     }
}
