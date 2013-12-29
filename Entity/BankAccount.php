<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

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
	 * @ORM\Column(name="slug", type="string", length=255, unique=true)
	 * @Gedmo\Slug(fields={"accountNumber"})
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
}