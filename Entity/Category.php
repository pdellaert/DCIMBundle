<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_categories")
 * @DoctrineAssert\UniqueEntity("title")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class Category
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
    protected $title;

    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"title"})
     * 
     * @var string
     */
    protected $slug;

    /**
     * @ORM\OneToMany(targetEntity="OutgoingInvoice", mappedBy="category")
     */
    protected $outgoingInvoices;

    /**
     * @ORM\OneToMany(targetEntity="incomingInvoice", mappedBy="category")
     */
    protected $incomingInvoices;
    
    
    public function __construct() {
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * Set title
     *
     * @param string $title
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * Add outgoingInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoices
     * @return Category
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
     * @param \Dellaert\DCIMBundle\Entity\incomingInvoice $incomingInvoices
     * @return Category
     */
    public function addIncomingInvoice(\Dellaert\DCIMBundle\Entity\incomingInvoice $incomingInvoices)
    {
        $this->incomingInvoices[] = $incomingInvoices;
    
        return $this;
    }

    /**
     * Remove incomingInvoices
     *
     * @param \Dellaert\DCIMBundle\Entity\incomingInvoice $incomingInvoices
     */
    public function removeIncomingInvoice(\Dellaert\DCIMBundle\Entity\incomingInvoice $incomingInvoices)
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