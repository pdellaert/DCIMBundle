<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_bankaccountentries")
 */
class BankAccountEntry
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
	 * @ORM\ManyToOne(targetEntity="BankAccount",inversedBy="bankAccountEntries")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\BankAccount")
	 */
	protected $bankAccount;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $targetAccount;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category",inversedBy="bankAccountEntries")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\Category")
	 */
	protected $category;

	/**
	 * @ORM\Column(type="datetime")
	 * 
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $amount;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $description;
	
	/**
	 * @ORM\ManyToOne(targetEntity="PersonalExpense",inversedBy="bankAccountEntries")
	 * @ORM\Column(nullable=true)
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\PersonalExpense")
	 */
	protected $personalExpense;
	
	/**
	 * @ORM\ManyToOne(targetEntity="PersonalRevenue",inversedBy="bankAccountEntries")
	 * @ORM\Column(nullable=true)
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\PersonalRevenue")
	 */
	protected $personalRevenue;
	
	/**
	 * @ORM\ManyToOne(targetEntity="IncomingInvoice",inversedBy="bankAccountEntries")
	 * @ORM\Column(nullable=true)
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\IncomingInvoice")
	 */
	protected $incomingInvoice;
	
	/**
	 * @ORM\ManyToOne(targetEntity="OutgoingInvoice",inversedBy="bankAccountEntries")
	 * @ORM\Column(nullable=true)
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\OutgoingInvoice")
	 */
	protected $outgoingInvoice;


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
     * @return BankAccountEntry
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
     * @return BankAccountEntry
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
     * @return BankAccountEntry
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
     * Set targetAccount
     *
     * @param string $targetAccount
     * @return BankAccountEntry
     */
    public function setTargetAccount($targetAccount)
    {
        $this->targetAccount = $targetAccount;

        return $this;
    }

    /**
     * Get targetAccount
     *
     * @return string 
     */
    public function getTargetAccount()
    {
        return $this->targetAccount;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return BankAccountEntry
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
     * Set amount
     *
     * @param float $amount
     * @return BankAccountEntry
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
     * Set description
     *
     * @param string $description
     * @return BankAccountEntry
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
     * Set personalExpense
     *
     * @param string $personalExpense
     * @return BankAccountEntry
     */
    public function setPersonalExpense($personalExpense)
    {
        $this->personalExpense = $personalExpense;

        return $this;
    }

    /**
     * Get personalExpense
     *
     * @return string 
     */
    public function getPersonalExpense()
    {
        return $this->personalExpense;
    }

    /**
     * Set personalRevenue
     *
     * @param string $personalRevenue
     * @return BankAccountEntry
     */
    public function setPersonalRevenue($personalRevenue)
    {
        $this->personalRevenue = $personalRevenue;

        return $this;
    }

    /**
     * Get personalRevenue
     *
     * @return string 
     */
    public function getPersonalRevenue()
    {
        return $this->personalRevenue;
    }

    /**
     * Set incomingInvoice
     *
     * @param string $incomingInvoice
     * @return BankAccountEntry
     */
    public function setIncomingInvoice($incomingInvoice)
    {
        $this->incomingInvoice = $incomingInvoice;

        return $this;
    }

    /**
     * Get incomingInvoice
     *
     * @return string 
     */
    public function getIncomingInvoice()
    {
        return $this->incomingInvoice;
    }

    /**
     * Set outgoingInvoice
     *
     * @param string $outgoingInvoice
     * @return BankAccountEntry
     */
    public function setOutgoingInvoice($outgoingInvoice)
    {
        $this->outgoingInvoice = $outgoingInvoice;

        return $this;
    }

    /**
     * Get outgoingInvoice
     *
     * @return string 
     */
    public function getOutgoingInvoice()
    {
        return $this->outgoingInvoice;
    }

    /**
     * Set createdBy
     *
     * @param \Dellaert\DCIMBundle\Entity\User $createdBy
     * @return BankAccountEntry
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
     * Set bankAccount
     *
     * @param \Dellaert\DCIMBundle\Entity\BankAccount $bankAccount
     * @return BankAccountEntry
     */
    public function setBankAccount(\Dellaert\DCIMBundle\Entity\BankAccount $bankAccount = null)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return \Dellaert\DCIMBundle\Entity\BankAccount 
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set category
     *
     * @param \Dellaert\DCIMBundle\Entity\Category $category
     * @return BankAccountEntry
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
}
