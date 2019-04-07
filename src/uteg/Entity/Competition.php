<?php

namespace uteg\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Doctrine\Common\Collections\Criteria;


/**
 * @ORM\Entity
 * @ORM\Table(name="ute_competition")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Competition
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="comp_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="gym")
     */
    protected $gym;

    /**
     * @ORM\Column(type="string", name="location")
     */
    protected $location;

    /**
     * @ORM\Column(type="integer", name="zipcode", length=4)
     * @Assert\Length(
     *      min = 4,
     *      max = 4
     * )
     */
    protected $zipcode;

    /**
     * @ORM\Column(type="date", name="startdate")
     */
    protected $startdate;

    /**
     * @ORM\Column(type="date", name="enddate")
     */
    protected $enddate;

    /**
     * @ORM\Column(type="integer", name="countCompetitionPlace")
     */
    protected $countCompetitionPlace;

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
     * @ORM\ManyToMany(targetEntity="User", mappedBy="competitions")
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="Starters2Competitions", mappedBy="competition", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $s2cs;

    /**
     * @ORM\OneToMany(targetEntity="Clubs2Invites", mappedBy="competition", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $c2is;

    /**
     * @ORM\OneToMany(targetEntity="Judges2Competitions", mappedBy="competition", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $j2cs;

    /**
     * @ORM\ManyToOne(targetEntity="Module", inversedBy="competitions", cascade={"persist"})
     * @ORM\JoinColumn(name="module_id", referencedColumnName="module_id")
     */
    protected $module;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="competition")
     */
    protected $departments;

    /**
     * @ORM\OneToMany(targetEntity="Grade", mappedBy="competition")
     */
    protected $grades;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->s2cs = new ArrayCollection();
        $this->c2is = new ArrayCollection();
        $this->j2cs = new ArrayCollection();
        $this->departments = new ArrayCollection();
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
     * @return Competition
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
     * Set gym
     *
     * @param string $gym
     * @return Competition
     */
    public function setGym($gym)
    {
        $this->gym = $gym;

        return $this;
    }

    /**
     * Get gym
     *
     * @return string
     */
    public function getGym()
    {
        return $this->gym;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Competition
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set zipcode
     *
     * @param integer $zipcode
     * @return Competition
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

    /**
     * Set startdate
     *
     * @param \DateTime $startdate
     * @return Competition
     */
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;

        return $this;
    }

    /**
     * Get startdate
     *
     * @return \DateTime
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * Set enddate
     *
     * @param \DateTime $enddate
     * @return Competition
     */
    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Get enddate
     *
     * @return \DateTime
     */
    public function getEnddate()
    {
        return $this->enddate;
    }

    /**
     * Set countCompetitionPlace
     *
     * @param integer $countCompetitionPlace
     * @return Competition
     */
    public function setCountCompetitionPlace($countCompetitionPlace) 
    {
        $this->countCompetitionPlace = $countCompetitionPlace;
        
        return $this;
    }

    /**
     * get countCompetitionPlace
     * 
     * @return integer
     */
    public function getCountCompetitionPlace()
    {
        return $this->countCompetitionPlace;
    }

    /**
     * Add users
     *
     * @param \uteg\Entity\User $users
     * @return Competition
     */
    public function addUser(\uteg\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \uteg\Entity\User $users
     */
    public function removeUser(\uteg\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Competition
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Competition
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
     * @param boolean $deletedAt
     * @return Competition
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return boolean
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add s2cs
     *
     * @param \uteg\Entity\Starters2Competitions $starters
     * @return Competition
     */
    public function addS2c(\uteg\Entity\Starters2Competitions $starters)
    {
        $this->s2cs[] = $starters;

        return $this;
    }

    /**
     * Remove s2cs
     *
     * @param \uteg\Entity\Starters2Competitions $starters
     */
    public function removeS2c(\uteg\Entity\Starters2Competitions $starters)
    {
        $this->s2cs->removeElement($starters);
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

    public function getS2csByGender($gender)
    {
        $return = array();

        foreach ($this->s2cs as $s2c) {
            if ($s2c->getStarterByGender($gender) !== null) {
                $return[] = $s2c;
            }
        }

        return $return;
    }

    public function getS2csByGenderCat($gender, $category)
    {
        $return = array();

        foreach ($this->s2cs as $s2c) {
            if ($s2c->getStarterByGenderCat($gender, $category) !== null) {
                $return[] = $s2c;
            }
        }

        return $return;
    }

    /**
     * Add c2is
     *
     * @param \uteg\Entity\Clubs2Invites $c2is
     * @return Competition
     */
    public function addC2i(\uteg\Entity\Clubs2Invites $c2is)
    {
        $this->c2is[] = $c2is;

        return $this;
    }

    /**
     * Remove c2is
     *
     * @param \uteg\Entity\Clubs2Invites $c2is
     */
    public function removeC2i(\uteg\Entity\Clubs2Invites $c2is)
    {
        $this->c2is->removeElement($c2is);
    }

    /**
     * Get c2is
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getC2is()
    {
        return $this->c2is;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setModule(\uteg\Entity\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Add departments
     *
     * @param \uteg\Entity\Department $departments
     * @return Competition
     */
    public function addDepartment(\uteg\Entity\Department $departments)
    {
        $this->departments[] = $departments;

        return $this;
    }

    /**
     * Remove departments
     *
     * @param \uteg\Entity\Department $departments
     */
    public function removeDepartment(\uteg\Entity\Department $departments)
    {
        $this->departments->removeElement($departments);
    }

    /**
     * Get departments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    public function getDepartmentsByCat(\uteg\Entity\Category $category)
    {
        $return = array();

        foreach ($this->departments as $department) {
            if ($department->getCategory() === $category) {
                $return[] = $department;
            }
        }

        return $return;
    }

    public function getDepartmentsByCatDate(\uteg\Entity\Category $category, \DateTime $dateTime)
    {
        $return = array();

        foreach ($this->departments as $department) {
            if ($department->getCategory() === $category && $department->getDate() == $dateTime) {
                $return[] = $department;
            }
        }

        return $return;
    }

    public function getDepartmentsByCatDateGender(\uteg\Entity\Category $category, \DateTime $dateTime, $gender)
    {
        $return = array();

        foreach ($this->departments as $department) {
            if ($department->getCategory() === $category && $department->getDate() == $dateTime && $department->getGender() == $gender) {
                $return[] = $department;
            }
        }

        return $return;
    }
    
    public function getDepartmentsByCatDateGenderCPlace(\uteg\Entity\Category $category, \DateTime $dateTime, $gender, $competitionPlace)
    {
        $return = array();

        foreach ($this->departments as $department) {
            if ($department->getCategory() === $category && $department->getDate() == $dateTime && $department->getGender() == $gender && $department->getCompetitionPlace() == $competitionPlace) {
                $return[] = $department;
            }
        }

        return $return;
    }

    public function getDepartmentsByCatGender(\uteg\Entity\Category $category, $gender)
    {
        $return = array();

        foreach ($this->departments as $department) {
            if ($department->getCategory() == $category && $department->getGender() == $gender) {
                $return[] = $department;
            }
        }

        return $return;
    }

    public function getDepartmentsByGender($gender)
    {
        $return = array();

        foreach ($this->departments as $department) {
            if ($department->getGender() == $gender) {
                $return[] = $department;
            }
        }

        return $return;
    }

    public function isDepOf(\uteg\Entity\Department $checkDep)
    {
        foreach ($this->departments as $department) {
            if ($department === $checkDep) {
                return true;
            }
        }

        return false;
    }

    public function isS2cOf(\uteg\Entity\Starters2Competitions $starters2Competitions)
    {
        foreach ($this->s2cs as $s2c) {
            if ($s2c === $starters2Competitions) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add j2cs
     *
     * @param \uteg\Entity\Judges2Competitions $j2cs
     * @return Competition
     */
    public function addJ2c(\uteg\Entity\Judges2Competitions $j2cs)
    {
        $this->j2cs[] = $j2cs;

        return $this;
    }

    /**
     * Remove j2cs
     *
     * @param \uteg\Entity\Judges2Competitions $j2cs
     */
    public function removeJ2c(\uteg\Entity\Judges2Competitions $j2cs)
    {
        $this->j2cs->removeElement($j2cs);
    }

    /**
     * Get j2cs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJ2cs()
    {
        return $this->j2cs;
    }

    public function isJ2cOf(\uteg\Entity\Judges2Competitions $check)
    {
        foreach ($this->j2cs as $j2c) {
            if ($j2c === $check) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add grades
     *
     * @param \uteg\Entity\Grade $grades
     * @return Competition
     */
    public function addGrade(\uteg\Entity\Grade $grades)
    {
        $this->grades[] = $grades;

        return $this;
    }

    /**
     * Remove grades
     *
     * @param \uteg\Entity\Grade $grades
     */
    public function removeGrade(\uteg\Entity\Grade $grades)
    {
        $this->grades->removeElement($grades);
    }

    /**
     * Get grades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrades()
    {
        return $this->grades;
    }
}
