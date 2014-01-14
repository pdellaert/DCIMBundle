<?php	
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_personalrevenues")
 * @DoctrineAssert\UniqueEntity("revenueNumber")
 * @DoctrineAssert\UniqueEntity("slug")
 */
class PersonalRevenue
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
	protected $payed;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category",inversedBy="personalRevenues")
	 * @Assert\Type(type="Dellaert\DCIMBundle\Entity\Category")
	 */
	protected $category;

	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 * @Assert\NotBlank()
	 */
	protected $revenueNumber;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @ORM\Column(name="slug", type="string", length=255, unique=true)
	 * @Gedmo\Slug(fields={"revenueNumber", "title"})
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
	protected $dueDate;

	/**
	 * @ORM\Column(type="float")
	 */
	protected $amount;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $filePath;

	/**
	 * @ORM\OneToMany(targetEntity="BankAccountEntry", mappedBy="bankAccount")
	 */
	protected $bankAccountEntries;
	
	/**
	 * @Assert\File(maxSize="104857600")
	 */
	protected $file;
	
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
		if (null !== $this->file) {
			$this->setFilePath($this->file->getClientOriginalName());
		}
	}

	public function postRemove()
	{
		if ($file = $this->getAbsolutePath()) {
			unlink($file);
			rmdir($this->getFileDir());
		}
	}
	
	/**
	 * Get due status
	 * 
	 * @return string green|warning|alert
	 */
	public function getDueState() {
		$now = new \DateTime;
		if( $now > $this->getDueDate() ) {
			return 'alert';
		}
		$weekBefore = clone $this->getDueDate();
		$weekBefore->sub(new \DateInterval('P7D'));
		if( $now > $weekBefore ) {
			return 'warning';
		}
		return 'green';
	}

	/**
	 * Get the file directory
	 * 
	 * @return string file
	 */
	public function getFileDir()
	{
		return __DIR__.'/../../../../data/personalrevenues/'.$this->getDate()->format('Y').'-'.$this->getRevenueNumber().'/';
	}

	/**
	 * Get absolute path to file
	 * 
	 * @return Ambigous <NULL, string>
	 */
	public function getAbsolutePath()
	{
		return null === $this->filePath ? null : $this->getFileDir().'/'.$this->filePath;
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
	 * @return PersonalRevenue
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
	 * @return PersonalRevenue
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
	 * @return PersonalRevenue
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
	 * Set payed
	 *
	 * @param boolean $payed
	 * @return PersonalRevenue
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
	 * Set revenueNumber
	 *
	 * @param string $revenueNumber
	 * @return PersonalRevenue
	 */
	public function setRevenueNumber($revenueNumber)
	{
		$this->revenueNumber = $revenueNumber;
	
		return $this;
	}

	/**
	 * Get invoiceNumber
	 *
	 * @return string 
	 */
	public function getRevenueNumber()
	{
		return $this->revenueNumber;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 * @return PersonalRevenue
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
	 * @return PersonalRevenue
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
	 * @return PersonalRevenue
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
	 * Set dueDate
	 *
	 * @param \DateTime $dueDate
	 * @return PersonalRevenue
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
	 * Set amount
	 *
	 * @param float $amount
	 * @return PersonalRevenue
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
	 * Set filePath
	 *
	 * @param string $filePath
	 * @return PersonalRevenue
	 */
	public function setFilePath($filePath)
	{
		$this->filePath = $filePath;
	
		return $this;
	}

	/**
	 * Get filePath
	 *
	 * @return string 
	 */
	public function getFilePath()
	{
		return $this->filePath;
	}

	/**
	 * Set file
	 *
	 * @param string $file
	 * @return PersonalRevenue
	 */
	public function setFile($file)
	{
		$this->file = $file;
	
		return $this;
	}

	/**
	 * Get file
	 *
	 * @return string 
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Set createdBy
	 *
	 * @param \Dellaert\DCIMBundle\Entity\User $createdBy
	 * @return PersonalRevenue
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
	 * @return PersonalRevenue
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
     * Add bankAccountEntries
     *
     * @param \Dellaert\DCIMBundle\Entity\BankAccountEntry $bankAccountEntries
     * @return PersonalRevenue
     */
    public function addBankAccountEntry(\Dellaert\DCIMBundle\Entity\BankAccountEntry $bankAccountEntries)
    {
        $this->bankAccountEntries[] = $bankAccountEntries;

        return $this;
    }

    /**
     * Remove bankAccountEntries
     *
     * @param \Dellaert\DCIMBundle\Entity\BankAccountEntry $bankAccountEntries
     */
    public function removeBankAccountEntry(\Dellaert\DCIMBundle\Entity\BankAccountEntry $bankAccountEntries)
    {
        $this->bankAccountEntries->removeElement($bankAccountEntries);
    }

    /**
     * Get bankAccountEntries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBankAccountEntries()
    {
        return $this->bankAccountEntries;
    }
}
