<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_outgoinginvoiceentries")
 */
class OutgoingInvoiceEntry
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
	 * @ORM\ManyToOne(targetEntity="OutgoingInvoice",inversedBy="entries")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\OutgoingInvoice")
	 */
	protected $outgoingInvoice;
	
	/**
	 * @ORM\ManyToOne(targetEntity="OutgoingInvoiceEntry",inversedBy="subentries")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry")
	 */
	protected $parentEntry;

	/**
	 * @ORM\OneToMany(targetEntity="OutgoingInvoiceEntry", mappedBy="parentEntry")
	 */
	protected $subentries;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $rate;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $amount;
	
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
	 * unset outgoingInvoice
	 *
	 */
	public function unsetOutgoingInvoice()
	{
		$this->outgoingInvoice = null;
	}

	public function getTotal() 
	{
		return number_format($this->getAmount()*$this->getRate(),2,'.','');
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
	 * @return OutgoingInvoiceEntry
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
	 * @return OutgoingInvoiceEntry
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
	 * @return OutgoingInvoiceEntry
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
	 * @return OutgoingInvoiceEntry
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
	 * Set rate
	 *
	 * @param float $rate
	 * @return OutgoingInvoiceEntry
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
	 * Set amount
	 *
	 * @param float $amount
	 * @return OutgoingInvoiceEntry
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
	
		return $this;
	}

	/**
	 * Get amount
	 *
	 * @return float 
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * Set createdBy
	 *
	 * @param \Dellaert\DCIMBundle\Entity\User $createdBy
	 * @return OutgoingInvoiceEntry
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
	 * Set outgoingInvoice
	 *
	 * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoice
	 * @return OutgoingInvoiceEntry
	 */
	public function setOutgoingInvoice(\Dellaert\DCIMBundle\Entity\OutgoingInvoice $outgoingInvoice = null)
	{
		$this->outgoingInvoice = $outgoingInvoice;
	
		return $this;
	}

	/**
	 * Get outgoingInvoice
	 *
	 * @return \Dellaert\DCIMBundle\Entity\OutgoingInvoice 
	 */
	public function getOutgoingInvoice()
	{
		return $this->outgoingInvoice;
	}

	/**
	 * Set parentEntry
	 *
	 * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $parentEntry
	 * @return OutgoingInvoiceEntry
	 */
	public function setParentEntry(\Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $parentEntry = null)
	{
		$this->parentEntry = $parentEntry;
	
		return $this;
	}

	/**
	 * Get parentEntry
	 *
	 * @return \Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry 
	 */
	public function getParentEntry()
	{
		return $this->parentEntry;
	}

	/**
	 * Add subentries
	 *
	 * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $subentries
	 * @return OutgoingInvoiceEntry
	 */
	public function addSubentrie(\Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $subentries)
	{
		$this->subentries[] = $subentries;
	
		return $this;
	}

	/**
	 * Remove subentries
	 *
	 * @param \Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $subentries
	 */
	public function removeSubentrie(\Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry $subentries)
	{
		$this->subentries->removeElement($subentries);
	}

	/**
	 * Get subentries
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getSubentries()
	{
		return $this->subentries;
	}
}