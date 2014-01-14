<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_bankaccounts")
 * @DoctrineAssert\UniqueEntity("accountNumber")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class BankAccount
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
	 * @ORM\Column(type="string", length=255, unique=true)
	 * @Assert\NotBlank()
	 * 
	 * @var string
	 */
	protected $accountNumber;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 * 
	 * @var string
	 */
	protected $accountName;

	/**
	 * @ORM\Column(type="boolean")
	 * 
	 * @var boolean
	 */
	protected $personal;

	/**
	 * @ORM\OneToMany(targetEntity="BankAccountEntry", mappedBy="bankAccount")
	 */
	protected $bankAccountEntries;

	/**
	 * @ORM\Column(name="slug", type="string", length=255, unique=true)
	 * @Gedmo\Slug(fields={"accountNumber"})
	 * 
	 * @var string
	 */
	protected $slug;
	
	public function __construct() {
		$this->bankAccountEntries = new ArrayCollection();
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
	 * @return BankAccount
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
	 * @return BankAccount
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
	 * @return BankAccount
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
	 * Set accountNumber
	 *
	 * @param string $accountNumber
	 * @return BankAccount
	 */
	public function setAccountNumber($accountNumber)
	{
		$this->accountNumber = $accountNumber;
	
		return $this;
	}

	/**
	 * Get accountNumber
	 *
	 * @return string 
	 */
	public function getAccountNumber()
	{
		return $this->accountNumber;
	}

	/**
	 * Set accountName
	 *
	 * @param string $accountName
	 * @return BankAccount
	 */
	public function setAccountName($accountName)
	{
		$this->accountName = $accountName;
	
		return $this;
	}

	/**
	 * Get accountName
	 *
	 * @return string 
	 */
	public function getAccountName()
	{
		return $this->accountName;
	}

	/**
	 * Set personal
	 *
	 * @param boolean $personal
	 * @return BankAccount
	 */
	public function setPersonal($personal)
	{
		$this->personal = $personal;
	
		return $this;
	}

	/**
	 * Get personal
	 *
	 * @return boolean 
	 */
	public function getPersonal()
	{
		return $this->personal;
	}

	/**
	 * Set slug
	 *
	 * @param string $slug
	 * @return BankAccount
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
	 * @return BankAccount
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
}
