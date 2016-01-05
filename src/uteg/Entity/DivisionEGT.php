<?php

namespace uteg\Entity;

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
  protected $starters;

  public function __construct()
  {
    $this->starters = new ArrayCollection();
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
}
