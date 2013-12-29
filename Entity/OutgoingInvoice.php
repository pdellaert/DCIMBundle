<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_outgoinginvoices")
 * @DoctrineAssert\UniqueEntity("invoiceNumber")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class OutgoingInvoice
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
	 * @ORM\Column(type="boolean")
	 * 
	 * @var boolean
	 */
	protected $generated;

	/**
	 * @ORM\Column(type="boolean")
	 * 
	 * @var boolean
	 */
	protected $payed;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category",inversedBy="outgoingInvoices")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\Category")
	 */
	protected $category;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Company",inversedBy="outgoingInvoices")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\Company")
	 */
	protected $originCompany;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Project",inversedBy="generatedInvoices")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\Project")
	 */
	protected $project;

	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 * @Assert\NotBlank()
	 */
	protected $invoiceNumber;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $fileLocation;

	/**
	 * @ORM\Column(name="slug", type="string", length=255, unique=true)
	 * @Gedmo\Slug(fields={"invoiceNumber", "title"})
	 * 
	 * @var string
	 */
	protected $slug;

	/**
	 * @ORM\Column(type="datetime")
	 * 
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @ORM\Column(type="datetime")
	 * 
	 * @var \DateTime
	 */
	protected $startDate;

	/**
	 * @ORM\Column(type="datetime")
	 * 
	 * @var \DateTime
	 */
	protected $endDate;

	/**
	 * @ORM\Column(type="datetime")
	 * 
	 * @var \DateTime
	 */
	protected $dueDate;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $vat;

	/**
	 * @ORM\OneToMany(targetEntity="OutgoingInvoiceEntry", mappedBy="outgoingInvoice")
	 */
	protected $entries;
	
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
	}

	/**
	 * Get total w/o VAT
	 * 
	 * @return double
	 */
	public function getTotalWoVAT() {
		$total = 0;
		foreach( $this->getEntries() as $entry ) {
			$total += $entry->getRate()*$entry->getAmount();
		}
		return number_format($total,2,'.','');
	}
	
	/**
	 * Get total VAT
	 * 
	 * @return double
	 */
	public function getTotalVAT() {
		$total = 0;
		foreach( $this->getEntries() as $entry ) {
			$total += $entry->getRate()*$entry->getAmount()*$this->getVat();
		}
		return number_format($total,2,'.','');
	}

	/**
	 * Get total w VAT
	 *
	 * @return double
	 */
	public function getTotalWithVAT()
	{
		return number_format(($this->getTotalWoVAT()+$this->getTotalVAT()),2,'.','');
	}
	
	/**
	 * Get due status
	 * 
	 * @return string green|warning|alert
	 */
	public function getDueState() {
		$now = new \DateTime;
		$prevWeek = new \DateTime;
		$prevWeek->sub(new \DateInterval('P7D'));
		if( $this->getDueDate() < $prevWeek ) {
			return 'alert';
		} elseif( $this->getDueDate() > $prevWeek && $this->getDueDate() < $now ) {
			return 'warning';
		} else {
			return 'green';
		}
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
	 * @return OutgoingInvoice
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
	 * @return OutgoingInvoice
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
	 * @return OutgoingInvoice
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
	 * Set generated
	 *
	 * @param boolean $generated
	 * @return OutgoingInvoice
	 */
	public function setGenerated($generated)
	{
		$this->generated = $generated;
	
		return $this;
	}

	/**
	 * Get generated
	 *
	 * @return boolean 
	 */
	public function getGenerated()
	{
		return $this->generated;
	}

	/**
	 * Set payed
	 *
	 * @param boolean $payed
	 * @return OutgoingInvoice
	 */
	public function setPayed($payed)
	{
		$this->payed = $payed;
	
		return $this;
	}

	/**
	 * Get payed
	 *
	 * @return boolean 
	 */
	public function getPayed()
	{
		return $this->payed;
	}

	/**
	 * Set invoiceNumber
	 *
	 * @param string $invoiceNumber
	 * @return OutgoingInvoice
	 */
	public function setInvoiceNumber($invoiceNumber)
	{
		$this->invoiceNumber = $invoiceNumber;
	
		return $this;
	}

	/**
	 * Get invoiceNumber
	 *
	 * @return string 
	 */
	public function getInvoiceNumber()
	{
		return $this->invoiceNumber;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 * @return OutgoingInvoice
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
	 * Set fileLocation
	 *
	 * @param string $fileLocation
	 * @return OutgoingInvoice
	 */
	public function setFileLocation($fileLocation)
	{
		$this->fileLocation = $fileLocation;
	
		return $this;
	}

	/**
	 * Get fileLocation
	 *
	 * @return string 
	 */
	public function getFileLocation()
	{
		return $this->fileLocation;
	}

	/**
	 * Set slug
	 *
	 * @param string $slug
	 * @return OutgoingInvoice
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
	 * Set date
	 *
	 * @param \DateTime $date
	 * @return OutgoingInvoice
	 */
	public function setDate($date)
	{
		$this->date = $date;
	
		return $this;
	}

	/**
	 * Get date
	 *
	 * @return \DateTime 
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * Set startDate
	 *
	 * @param \DateTime $startDate
	 * @return OutgoingInvoice
	 */
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
	
		return $this;
	}

	/**
	 * Get startDate
	 *
	 * @return \DateTime 
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * Set endDate
	 *
	 * @param \DateTime $endDate
	 * @return OutgoingInvoice
	 */
	public function setEndDate($endDate)
	{
		$this->endDate = $endDate;
	
		return $this;
	}

	/**
	 * Get endDate
	 *
	 * @return \DateTime 
	 */
	public function getEndDate()
	{
		return $this->endDate;
	}

	/**
	 * Set dueDate
	 *
	 * @param \DateTime $dueDate
	 * @return OutgoingInvoice
	 */
	public function setDueDate($dueDate)
	{
		$this->dueDate = $dueDate;
	
		return $this;
	}

	/**
	 * Get dueDate
	 *
	 * @return \DateTime 
	 */
	public function getDueDate()
	{
		return $this->dueDate;
	}

	/**
	 * Set vat
	 *
	 * @param float $vat
	 * @return OutgoingInvoice
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
	 * Set createdBy
	 *
	 * @param \Dellaert\DCIMBundle\Entity\User $createdBy
	 * @return OutgoingInvoice
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
	 * Set category
	 *
	 * @param \Dellaert\DCIMBundle\Entity\Category $category
	 * @return OutgoingInvoice
	 */
	public function setCategory(\Dellaert\DCIMBundle\Entity\Category $category = null)
	{
		$this->category = $category;
	
		return $this;
	}

	/**
	 * Get category
	 *
	 * @return \Dellaert\DCIMBundle\Entity\Category 
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * Set originCompany
	 *
	 * @param \Dellaert\DCIMBundle\Entity\Company $originCompany
	 * @return OutgoingInvoice
	 */
	public function setOriginCompany(\Dellaert\DCIMBundle\Entity\Company $originCompany = null)
	{
		$this->originCompany = $originCompany;
	
		return $this;
	}

	/**
	 * Get originCompany
	 *
	 * @return \Dellaert\DCIMBundle\Entity\Company 
	 */
	public function getOriginCompany()
	{
		return $this->originCompany;
	}

	/**
	 * Set project
	 *
	 * @param \Dellaert\DCIMBundle\Entity\Project $project
	 * @return OutgoingInvoice
	 */
	public function setProject(\Dellaert\DCIMBundle\Entity\Project $project = null)
	{
		$this->project = $project;
	
		return $this;
	}

	/**
	 * Get project
	 *
	 * @return \Dellaert\DCIMBundle\Entity\Project 
	 */
	public function getProject()
	{
		return $this->project;
	}

	/**
	 * Add entries
	 *
	 * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $entries
	 * @return OutgoingInvoice
	 */
	public function addEntrie(\Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $entries)
	{
		$this->entries[] = $entries;
	
		return $this;
	}

	/**
	 * Remove entries
	 *
	 * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $entries
	 */
	public function removeEntrie(\Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $entries)
	{
		$this->entries->removeElement($entries);
	}

	/**
	 * Get entries
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getEntries()
	{
		return $this->entries;
	}
}