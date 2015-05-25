<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_starter")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Starter {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="starter_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="firstname")
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", name="lastname")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="integer", name="birthyear", length=4)
     * @Assert\Length(
     *      min = 4,
     *      max = 4
     * )
     */
    protected $birthyear;

    /**
     * @ORM\OnetoMany(targetEntity="Starters2Competitions", mappedBy="starter", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $competitions;

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
        $this->competitions = new ArrayCollection();
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
     * Set firstname
     *
     * @param string $firstname
     * @return Starter
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Starter
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthyear
     *
     * @param integer $birthyear
     * @return Starter
     */
    public function setBirthyear($birthyear)
    {
        $this->birthyear = $birthyear;

        return $this;
    }

    /**
     * Get birthyear
     *
     * @return integer 
     */
    public function getBirthyear()
    {
        return $this->birthyear;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Starter
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
     * @return Starter
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
     * Add competitions
     *
     * @param \AppBundle\Entity\Starters2Competitions $competitions
     * @return Starter
     */
    public function addCompetition(\AppBundle\Entity\Starters2Competitions $competitions)
    {
        $this->competitions[] = $competitions;

        return $this;
    }

    /**
     * Remove competitions
     *
     * @param \AppBundle\Entity\Starters2Competitions $competitions
     */
    public function removeCompetition(\AppBundle\Entity\Starters2Competitions $competitions)
    {
        $this->competitions->removeElement($competitions);
    }

    /**
     * Get competitions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompetitions()
    {
        return $this->competitions;
    }
}
