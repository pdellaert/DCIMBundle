<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_companies")
 * @DoctrineAssert\UniqueEntity("companyName")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class Company
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
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * 
     * @var string
     */
    protected $companyName;

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
    protected $centralTelephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email(
     * 		message = "The email '{{ value }}' is not a valid email.",
     * 		checkMX = true
     * )
     * 
     * @var string
     */
    protected $centralEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @var string
     */
    protected $vatNumber;

    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"companyName"})
     * 
     * @var string
     */
    protected $slug;

    /**
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="company")
     */
    protected $contacts;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="company")
     */
    protected $projects;

    /**
     * @ORM\OneToMany(targetEntity="OutgoingInvoice", mappedBy="originCompany")
     */
    protected $outgoingInvoices;

    /**
     * @ORM\OneToMany(targetEntity="IncomingInvoice", mappedBy="targetCompany")
     */
    protected $incomingInvoices;
    
    public function __construct() {
    	$this->contacts = new ArrayCollection();
    	$this->projects = new ArrayCollection();
    	$this->outgoingInvoices = new ArrayCollection();
    	$this->incomingInvoices = new ArrayCollection();
    }
    
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
     * Get total revenue (w/o VAT)
     * 
     * @return double
     */
    public function getTotalRevenue($year = -1)
    {
    	if( $year == -1 ) {
    		$now = new \DateTime();
    		$year = $now->format('Y');
    	}
    	$total = 0;
    	foreach( $this->getOutgoingInvoices() as $invoice ) {
    		if( $invoice->getDate()->format('Y') == $year ) {
    			$total += $invoice->getTotalWoVAT();
    		}
    	}
    	return number_format($total,2,'.','');
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * Set companyName
     *
     * @param string $companyName
     * @return Company
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    
        return $this;
    }

    /**
     * Get companyName
     *
     * @return string 
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * @return Company
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
     * Set centralTelephone
     *
     * @param string $centralTelephone
     * @return Company
     */
    public function setCentralTelephone($centralTelephone)
    {
        $this->centralTelephone = $centralTelephone;
    
        return $this;
    }

    /**
     * Get centralTelephone
     *
     * @return string 
     */
    public function getCentralTelephone()
    {
        return $this->centralTelephone;
    }

    /**
     * Set centralEmail
     *
     * @param string $centralEmail
     * @return Company
     */
    public function setCentralEmail($centralEmail)
    {
        $this->centralEmail = $centralEmail;
    
        return $this;
    }

    /**
     * Get centralEmail
     *
     * @return string 
     */
    public function getCentralEmail()
    {
        return $this->centralEmail;
    }

    /**
     * Set vatNumber
     *
     * @param string $vatNumber
     * @return Company
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;
    
        return $this;
    }

    /**
     * Get vatNumber
     *
     * @return string 
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Company
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
     * @return Company
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
     * Add contacts
     *
     * @param \Dellaert\DCIMBundle\Entity\Contact $contacts
     * @return Company
     */
    public function addContact(\Dellaert\DCIMBundle\Entity\Contact $contacts)
    {
        $this->contacts[] = $contacts;
    
        return $this;
    }

    /**
     * Remove contacts
     *
     * @param \Dellaert\DCIMBundle\Entity\Contact $contacts
     */
    public function removeContact(\Dellaert\DCIMBundle\Entity\Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add projects
     *
     * @param \Dellaert\DCIMBundle\Entity\Project $projects
     * @return Company
     */
    public function addProject(\Dellaert\DCIMBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;
    
        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Dellaert\DCIMBundle\Entity\Project $projects
     */
    public function removeProject(\Dellaert\DCIMBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add outgoingInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoices
     * @return Company
     */
    public function addOutgoingInvoice(\Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoices)
    {
        $this->outgoingInvoices[] = $outgoingInvoices;
    
        return $this;
    }

    /**
     * Remove outgoingInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoices
     */
    public function removeOutgoingInvoice(\Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoices)
    {
        $this->outgoingInvoices->removeElement($outgoingInvoices);
    }

    /**
     * Get outgoingInvoices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOutgoingInvoices()
    {
        return $this->outgoingInvoices;
    }

    /**
     * Add incomingInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\IncomingInvoice $incomingInvoices
     * @return Company
     */
    public function addIncomingInvoice(\Dellaert\DCIMBundle\Entity\IncomingInvoice $incomingInvoices)
    {
        $this->incomingInvoices[] = $incomingInvoices;
    
        return $this;
    }

    /**
     * Remove incomingInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\IncomingInvoice $incomingInvoices
     */
    public function removeIncomingInvoice(\Dellaert\DCIMBundle\Entity\IncomingInvoice $incomingInvoices)
    {
        $this->incomingInvoices->removeElement($incomingInvoices);
    }

    /**
     * Get incomingInvoices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIncomingInvoices()
    {
        return $this->incomingInvoices;
    }
}