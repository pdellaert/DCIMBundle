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

}
