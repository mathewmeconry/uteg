<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_starter")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Starter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="starter_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="stvid", nullable=true)
     */
    protected $stvid;

    /**
     * @ORM\Column(type="string", name="firstname")
     * @Assert\NotBlank(message="starter.error.firstname")
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", name="lastname")
     * @Assert\NotBlank(message="starter.error.lastname")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", name="gender")
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"male", "female"}, message = "starter.error.gender")
     */
    protected $gender;

    /**
     * @ORM\Column(type="integer", name="birthyear", length=4)
     * @Assert\NotBlank(message="starter.error.birthyear")
     * @Assert\Length(
     *      min = 4,
     *      max = 4,
     *      minMessage="starter.error.birthyearMin",
     *      maxMessage="starter.error.birthyearMax"
     * )
     */
    protected $birthyear;

    /**
     * @ORM\OneToMany(targetEntity="Starters2Competitions", mappedBy="starter", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $s2cs;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    public function __construct()
    {
        $this->s2cs = new ArrayCollection();
    }

    public function __toArray()
    {
        $array['stvid'] = $this->stvid;
        $array['firstname'] = $this->firstname;
        $array['lastname'] = $this->lastname;
        $array['birthyear'] = $this->birthyear;
        $array['gender'] = $this->gender;
        return $array;
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

    public function setStvid($stvid) {
        $this->stvid = $stvid;
        return $this;
    }

    public function getStvid() {
        return $this->stvid;
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
     * Add s2c
     *
     * @param \uteg\Entity\Starters2Competitions $s2c
     * @return Starter
     */
    public function addS2c(\uteg\Entity\Starters2Competitions $s2c)
    {
        $this->s2cs[] = $s2c;

        return $this;
    }

    /**
     * Remove s2c
     *
     * @param \uteg\Entity\Starters2Competitions $s2cs
     */
    public function removeS2c(\uteg\Entity\Starters2Competitions $s2c)
    {
        $this->s2cs->removeElement($s2c);
    }

    /**
     * Get s2cs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getS2cs()
    {
        return $this->s2cs;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return Starter
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

}
