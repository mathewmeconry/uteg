<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_category")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Category {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="cat_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name")
     */
    protected $name;

    /**
     * @ORM\OnetoMany(targetEntity="Starters2Competitions", mappedBy="category")
     */
    protected $starters;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    public function __construct() {
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
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Category
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
     * @return Category
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
     * Add starters
     *
     * @param \AppBundle\Entity\Starters2Competitions $starters
     * @return Category
     */
    public function addStarter(\AppBundle\Entity\Starters2Competitions $starters)
    {
        $this->starters[] = $starters;

        return $this;
    }

    /**
     * Remove starters
     *
     * @param \AppBundle\Entity\Starters2Competitions $starters
     */
    public function removeStarter(\AppBundle\Entity\Starters2Competitions $starters)
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
