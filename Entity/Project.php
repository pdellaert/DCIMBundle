<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_projects")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class Project
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
     * @ORM\ManyToOne(targetEntity="Company",inversedBy="projects")
     * @Assert\Type(type="Dellaert\DCIMBundle\Entity\Company")
     */
    protected $company;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $companyNum;

    /**
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="project")
     */
    protected $issues;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"companyNum", "title"})
     * 
     * @var string
     */
    protected $slug;

    /**
     * @ORM\Column(type="float")
     */
    protected $rate;

    /**
     * @ORM\Column(type="float")
     */
    protected $vat;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="OutgoingInvoice", mappedBy="project")
     */
    protected $generatedInvoices;
    
    public function __construct() {
    	$this->issues = new ArrayCollection();
    }
    
    public function preInsert()
    {
    	$this->createdAt = new \DateTime();
    	$this->preUpdate();
    }
    
    public function preUpdate()
    {
    	$this->updatedAt = new \DateTime();
    	$this->companyNum = $this->company->getId();
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
     * @return Project
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
     * @return Project
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
     * @return Project
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
     * Set companyNum
     *
     * @param string $companyNum
     * @return Project
     */
    public function setCompanyNum($companyNum)
    {
        $this->companyNum = $companyNum;
    
        return $this;
    }

    /**
     * Get companyNum
     *
     * @return string 
     */
    public function getCompanyNum()
    {
        return $this->companyNum;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Project
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Project
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
     * Set rate
     *
     * @param float $rate
     * @return Project
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    
        return $this;
    }

    /**
     * Get rate
     *
     * @return float 
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set vat
     *
     * @param float $vat
     * @return Project
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    
        return $this;
    }

    /**
     * Get vat
     *
     * @return float 
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdBy
     *
     * @param \Dellaert\DCIMBundle\Entity\User $createdBy
     * @return Project
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
     * @return Project
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

    /**
     * Add issues
     *
     * @param \Dellaert\DCIMBundle\Entity\Issue $issues
     * @return Project
     */
    public function addIssue(\Dellaert\DCIMBundle\Entity\Issue $issues)
    {
        $this->issues[] = $issues;
    
        return $this;
    }

    /**
     * Remove issues
     *
     * @param \Dellaert\DCIMBundle\Entity\Issue $issues
     */
    public function removeIssue(\Dellaert\DCIMBundle\Entity\Issue $issues)
    {
        $this->issues->removeElement($issues);
    }

    /**
     * Get issues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Add generatedInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoice $generatedInvoices
     * @return Project
     */
    public function addGeneratedInvoice(\Dellaert\DCIMBundle\Entity\OutgoingInvoice $generatedInvoices)
    {
        $this->generatedInvoices[] = $generatedInvoices;
    
        return $this;
    }

    /**
     * Remove generatedInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoice $generatedInvoices
     */
    public function removeGeneratedInvoice(\Dellaert\DCIMBundle\Entity\OutgoingInvoice $generatedInvoices)
    {
        $this->generatedInvoices->removeElement($generatedInvoices);
    }

    /**
     * Get generatedInvoices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGeneratedInvoices()
    {
        return $this->generatedInvoices;
    }
}