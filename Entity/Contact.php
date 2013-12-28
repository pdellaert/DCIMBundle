<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_contacts")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * 
     * @var \DateTime
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="contacts")
     */
    protected $company;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * 
     * @var string
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * 
     * @var string
     */
    protected $lastname;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $streetnumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $postalcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email()
     * 
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"firstname", "lastname","postalcode"})
     * 
     * @var string
     */
    protected $slug;
    
    public function preInsert()
    {
    	$this->createdAt = new \DateTime();
    	$this->preUpdate();
    }
    
    public function preUpdate()
    {
    	$this->updatedAt = new \DateTime();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Contact
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Contact
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Contact
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Contact
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
     * @return Contact
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
     * @return Contact
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
     * Set streetnumber
     *
     * @param string $streetnumber
     * @return Contact
     */
    public function setStreetnumber($streetnumber)
    {
        $this->streetnumber = $streetnumber;
    
        return $this;
    }

    /**
     * Get streetnumber
     *
     * @return string 
     */
    public function getStreetnumber()
    {
        return $this->streetnumber;
    }

    /**
     * Set postalcode
     *
     * @param string $postalcode
     * @return Contact
     */
    public function setPostalcode($postalcode)
    {
        $this->postalcode = $postalcode;
    
        return $this;
    }

    /**
     * Get postalcode
     *
     * @return string 
     */
    public function getPostalcode()
    {
        return $this->postalcode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Contact
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
     * Set country
     *
     * @param string $country
     * @return Contact
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return Contact
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    
        return $this;
    }

    /**
     * Get telephone
     *
     * @return string 
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Contact
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
     * Set slug
     *
     * @param string $slug
     * @return Contact
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set createdBy
     *
     * @param \Dellaert\DCIMBundle\Entity\User $createdBy
     * @return Contact
     */
    public function setCreatedBy(\Dellaert\DCIMBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Dellaert\DCIMBundle\Entity\User 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set company
     *
     * @param \Dellaert\DCIMBundle\Entity\Company $company
     * @return Contact
     */
    public function setCompany(\Dellaert\DCIMBundle\Entity\Company $company = null)
    {
        $this->company = $company;
    
        return $this;
    }

    /**
     * Get company
     *
     * @return \Dellaert\DCIMBundle\Entity\Company 
     */
    public function getCompany()
    {
        return $this->company;
    }
}