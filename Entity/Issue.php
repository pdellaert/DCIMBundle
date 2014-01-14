<?php
namespace Dellaert\DCIMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="dcim_issues")
 */
class Issue
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
	 * @ORM\ManyToOne(targetEntity="Project",inversedBy="issues")
	 */
	protected $project;

	/**
	 * @ORM\OneToMany(targetEntity="WorkEntry", mappedBy="issue")
	 */
	protected $workentries;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	protected $rate;

	/**
	 * @ORM\Column(type="boolean")
	 * 
	 * @var boolean
	 */
	protected $statsShowListOnly;
	
	public function __construct() {
		$this->workentries = new ArrayCollection();
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
	 * Get total of amount worked
	 * 
	 * @return float
	 */
	public function getTotalWorkEntryAmount()
	{
		$total = 0;
		$workentries = $this->getWorkEntries();
		foreach( $workentries as $entry ) {
			$total += $entry->getAmount();
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
	 * @return Issue
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
	 * @return Issue
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
	 * @return Issue
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
	 * @return Issue
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
	 * Set description
	 *
	 * @param string $description
	 * @return Issue
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
	 * Set rate
	 *
	 * @param float $rate
	 * @return Issue
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
	 * Set statsShowListOnly
	 *
	 * @param boolean $statsShowListOnly
	 * @return Issue
	 */
	public function setStatsShowListOnly($statsShowListOnly)
	{
		$this->statsShowListOnly = $statsShowListOnly;
	
		return $this;
	}

	/**
	 * Get statsShowListOnly
	 *
	 * @return boolean 
	 */
	public function getStatsShowListOnly()
	{
		return $this->statsShowListOnly;
	}

	/**
	 * Set createdBy
	 *
	 * @param \Dellaert\DCIMBundle\Entity\User $createdBy
	 * @return Issue
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
	 * Set project
	 *
	 * @param \Dellaert\DCIMBundle\Entity\Project $project
	 * @return Issue
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
	 * Add workentries
	 *
	 * @param \Dellaert\DCIMBundle\Entity\WorkEntry $workentries
	 * @return Issue
	 */
	public function addWorkentrie(\Dellaert\DCIMBundle\Entity\WorkEntry $workentries)
	{
		$this->workentries[] = $workentries;
	
		return $this;
	}

	/**
	 * Remove workentries
	 *
	 * @param \Dellaert\DCIMBundle\Entity\WorkEntry $workentries
	 */
	public function removeWorkentrie(\Dellaert\DCIMBundle\Entity\WorkEntry $workentries)
	{
		$this->workentries->removeElement($workentries);
	}

	/**
	 * Get workentries
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getWorkentries()
	{
		return $this->workentries;
	}

    /**
     * Add workentries
     *
     * @param \Dellaert\DCIMBundle\Entity\WorkEntry $workentries
     * @return Issue
     */
    public function addWorkentry(\Dellaert\DCIMBundle\Entity\WorkEntry $workentries)
    {
        $this->workentries[] = $workentries;

        return $this;
    }

    /**
     * Remove workentries
     *
     * @param \Dellaert\DCIMBundle\Entity\WorkEntry $workentries
     */
    public function removeWorkentry(\Dellaert\DCIMBundle\Entity\WorkEntry $workentries)
    {
        $this->workentries->removeElement($workentries);
    }
}
