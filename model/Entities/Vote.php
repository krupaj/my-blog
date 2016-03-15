<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use	Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Vote
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="vote")
 */
class Vote {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="vote_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Otazka ankety
	 */
	protected $question;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Model\Entities\TypeVote", inversedBy="votes")
	 * @ORM\JoinColumn(name="type_vote_id", referencedColumnName="type_vote_id")
	 * @var TypeVote 
	 */
	protected $typeVote;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var DateTime Datum expirace ankety
	 */
	protected $expire;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Model\Entities\Option", cascade={"persist"})
	 * @ORM\JoinTable(name="vote_option",
     *      joinColumns={@ORM\JoinColumn(name="vote_id", referencedColumnName="vote_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="option_id", unique=true)}
     *      )
	 * @var ArrayCollection Option[]
	 */
	protected $options;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Model\Entities\Poll", cascade={"persist"})
	 * @ORM\JoinTable(name="vote_poll",
     *      joinColumns={@ORM\JoinColumn(name="vote_id", referencedColumnName="vote_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="poll_id", referencedColumnName="poll_id", unique=true)}
     *      )
	 * @var ArrayCollection Poll[]
	 */
	protected $polls;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Model\Entities\Article", inversedBy="votes", cascade={"persist"})
	 * @ORM\JoinTable(name="article_vote",
     *      joinColumns={@ORM\JoinColumn(name="vote_id", referencedColumnName="vote_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="article_id", unique=true)}
     *      )
	 * @var ArrayCollection Article[]
	 */
	protected $articles;

	/**
	 * @param string $question
	 */
	public function __construct($question) {
		$this->question = $question;
		$this->options = new ArrayCollection;
		$this->polls = new ArrayCollection;
		$this->articles = new ArrayCollection;
	}
	
	public function setQuestion($question) {
		$this->question = $question;
	}
	
	/**
	 * Nastavuje typ anketni otazky
	 * @param \App\Model\Entities\TypeVote $typeVote
	 */
	public function setTypeVote(TypeVote $typeVote) {
		$this->typeVote = $typeVote;
	}
	
	/**
	 * Nastavuje platnost ankety
	 * @param DateTime $expiration
	 */
	public function setExpire(DateTime $expiration) {
		$this->expire = $expiration;
	}
	
	/**
	 * Pridava odpoved k otazce
	 * @param \App\Model\Entities\Option $option
	 */
	public function addOption(Option $option) {
		if (!$this->options->contains($option)) {
			$this->options->add($option);
		}
	}
	
	/**
	 * Odebira odpoved od otazky
	 * @param \App\Model\Entities\Option $option
	 */
	public function removeOption(Option $option) {
		if ($this->options->contains($option)) {
			$this->options->removeElement($option);
		}
	}
	
	/**
	 * Nastavuje odpovedi k anketni otazce
	 * @param string[]
	 * @return array Klic remove obsahuje Option ke smazani
	 */
	public function setOptions($options) {
		$result = ['remove' => []];
		foreach ($this->getOptions() as $option) {
			$key = array_search($option->getValue(), $options);
			if ($key === FALSE) {
				//odstranit, key se cisluje od nuly
				$this->removeOption($option);
				$result['remove'][] = $option;
			} else {
				//odpoved je jiz prirazena
				unset($options[$key]);
			}
		}
		//pridat vsechny nove zbyvajici
		foreach ($options as $option) {
			$newOption = new Option($option);
			$this->addOption($newOption);
		}
		return $result;
	}
	
	/**
	 * @return int vote_id pkey
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param int|NULL $limit Maximalni delka retezce otazky
	 * @return string Otazka ankety
	 */
	public function getQuestion($limit = NULL) {
		if ($limit !== NULL) {
			return Strings::truncate($this->question, $limit);
		}
		return $this->question;
	}
	
	/**
	 * @return TypeVote Typ ankety/otazky
	 */
	public function getTypeVote() {
		return $this->typeVote;
	}
	
	/**
	 * @return DateTime|NULL Datum vyprseni platnosti ankety
	 */
	public function getExpire() {
		return $this->expire;
	}
	
	/**
	 * Vraci odpovedi anketni otazky
	 * @return Option[]
	 */
	public function getOptions() {
		if ($this->options === NULL) {
			$this->options = new ArrayCollection;
		}
		return $this->options;
	}
	
	/**
	 * Vraci hlasovani na anketni otazky
	 * @return Poll[]
	 */
	public function getPolls() {
		if ($this->polls === NULL) {
			$this->polls = new ArrayCollection;
		}
		return $this->polls;
	}
	
	/**
	 * Pridava hlasovani k otazce
	 * @param \App\Model\Entities\Poll $poll
	 */
	public function addPoll(Poll $poll) {
		if (!$this->polls->contains($poll)) {
			$this->polls->add($poll);
		}
	}
	
	public function getPollResult() {
		$options = $this->getOptions();
		$result = [ 'total' => 0 ];
		foreach ($options as $option) {
			if ($option === NULL) {
				continue;
			}
			$result['options'][$option->getId()] = [
				'option' => $option->getValue(),
				'total' => 0
			];
		}
		$lastVoterId = NULL;
		foreach ($this->getPolls() as $poll) {
			if ($poll->getOption() === NULL) {
				continue;
			}
			if ($lastVoterId != $poll->getVoteIdentification()) {
				$lastVoterId = $poll->getVoteIdentification();
				$result['total']++;
			}
			$optionId = $poll->getOption()->getId();
			$result['options'][$optionId]['total']++;
		}
		
		$perTotal = ($result['total'] == 0) ? 0 : (100 / $result['total']);
		foreach ($result['options'] as $optionId => $option) {
			$result['options'][$optionId]['per'] = ( $perTotal * $option['total'] );
			
		}
		return $result;
	}
	
	/**
	 * Vraci clanky asociovane s anketou
	 * @return Article[]
	 */
	public function getArticles() {
		if ($this->articles === NULL) {
			$this->articles = new ArrayCollection;
		}
		return $this->articles;
	}
	
	/**
	 * Pridava propojeni clanku a ankety
	 * @param \App\Model\Entities\Article $article
	 */
	public function addArticle(Article $article) {
		if (!$this->getArticles()->contains($article)) {
			$this->articles->add($article);
		}
	}
	
	/**
	 * Odstranuje propojeni clanku a ankety
	 * @param \App\Model\Entities\Article $article
	 */
	public function removeArticle(Article $article) {
		if ($this->getArticles()->contains($article)) {
			$this->articles->removeElement($article);
		}
	}
}

