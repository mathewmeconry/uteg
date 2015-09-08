<?php

namespace Uteg\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Doctrine\Common\Collections\Criteria;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_module")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Module {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="mod_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="display_name")
     */
    protected $dispName;

    /**
     * @ORM\Column(type="string", name="bundle_name")
     */
    protected $bundName;

    /**
     * @ORM\OneToMany(targetEntity="Competition", mappedBy="module", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $comps;

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
     * Constructor
     */
    public function __construct()
    {
        $this->comps = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set dispName
     *
     * @param string $dispName
     * @return Module
     */
    public function setDispName($dispName)
    {
        $this->dispName = $dispName;
    
        return $this;
    }

    /**
     * Get dispName
     *
     * @return string 
     */
    public function getDispName()
    {
        return $this->dispName;
    }

    /**
     * Set bundName
     *
     * @param string $bundName
     * @return Module
     */
    public function setBundName($bundName)
    {
        $this->bundName = $bundName;
    
        return $this;
    }

    /**
     * Get bundName
     *
     * @return string 
     */
    public function getBundName()
    {
        return $this->bundName;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Module
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
     * @return Module
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
     * Add comps
     *
     * @param \Uteg\BaseBundle\Entity\Competition $comps
     * @return Module
     */
    public function addComp(\Uteg\BaseBundle\Entity\Competition $comps)
    {
        $this->comps[] = $comps;
    
        return $this;
    }

    /**
     * Remove comps
     *
     * @param \Uteg\BaseBundle\Entity\Competition $comps
     */
    public function removeComp(\Uteg\BaseBundle\Entity\Competition $comps)
    {
        $this->comps->removeElement($comps);
    }

    /**
     * Get comps
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComps()
    {
        return $this->comps;
    }

    public function __toString()
    {
        return $this->getDispName();
    }
}
