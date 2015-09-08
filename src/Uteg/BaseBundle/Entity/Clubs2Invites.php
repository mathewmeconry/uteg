<?php

namespace Uteg\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_club2invites")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Clubs2Invites
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="c2i_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Club", inversedBy="c2is", cascade={"persist"})
     * @ORM\JoinColumn(name="club_id", referencedColumnName="club_id")
     */
    protected $club;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="c2is", cascade={"persist"})
     * @ORM\JoinColumn(name="comp_id", referencedColumnName="comp_id")
     */
    protected $competition;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="invites.error.firstname")
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="invites.error.lastname")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="invites.error.email")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="invites.error.street")
     */
    protected $street;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="invites.error.city")
     */
    protected $city;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="invites.error.zipcode")
     * @Assert\Length(
     *      min = 4,
     *      max = 4,
     *      minMessage="invites.error.zipcodeMin",
     *      maxMessage="invites.error.zipcodeMax"
     * )
     */
    protected $zipcode;

    /**
     * @ORM\Column(type="string")
     */
    protected $token;

    /**
     * @ORM\Column(type="date")
     */
    protected $valid;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", name="deletedAt", nullable=true)
     */
    protected $deletedAt;

    public function __toString()
    {
        return $this->name;
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
     * @return Club
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
     * @return Club
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
     * @return Club
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
     * Add members
     *
     * @param \Uteg\BaseBundle\Entity\Starters2Competitions $members
     * @return Club
     */
    public function addMember(\Uteg\BaseBundle\Entity\Starters2Competitions $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \Uteg\BaseBundle\Entity\Starters2Competitions $members
     */
    public function removeMember(\Uteg\BaseBundle\Entity\Starters2Competitions $members)
    {
        $this->members->removeElement($members);

        return $this->members;
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set club
     *
     * @param \Uteg\BaseBundle\Entity\Club $club
     * @return Clubs2Invites
     */
    public function setClub(\Uteg\BaseBundle\Entity\Club $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Uteg\BaseBundle\Entity\Club
     */
    public function getClub()
    {
        return $this->club->getName();
    }

    public function getClubObj()
    {
        return $this->club;
    }

    /**
     * Set competition
     *
     * @param \Uteg\BaseBundle\Entity\Competition $competition
     * @return Clubs2Invites
     */
    public function setCompetition(\Uteg\BaseBundle\Entity\Competition $competition = null)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Get competition
     *
     * @return \Uteg\BaseBundle\Entity\Competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Clubs2Invites
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set valid
     *
     * @param \Date $valid
     * @return Clubs2Invites
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return \Date
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Clubs2Invites
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Clubs2Invites
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
     * @return Clubs2Invites
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
     * Set street
     *
     * @param string $street
     * @return Clubs2Invites
     */
    public function setStreet($street)
    {
        $this->street = $street;
    
        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Clubs2Invites
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zipcode
     *
     * @param integer $zipcode
     * @return Clubs2Invites
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    
        return $this;
    }

    /**
     * Get zipcode
     *
     * @return integer 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }
}
