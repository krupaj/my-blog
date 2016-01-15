<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Nette\Utils\DateTime;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class Comment
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="comment_article")
 */
class Comment {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="comment_article_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Model\Entities\Article", cascade={"persist"})
	 * @ORM\JoinColumn(name="article_id", referencedColumnName="article_id")
	 * @var Article
	 */
	private $article;

	/**
	 * @ORM\Column(type="string", name="user_name", nullable=true)
	 * @var string Nazev weboveho uzivatele, jenz komentar vytvoril
	 */
	protected $userName;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Model\Entities\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
	 * @var User Uzivatel systemu, jenz komentar vytvoril
	 */
	protected $user;

	/**
	 * @ORM\Column(type="string", nullable=false, name="content")
	 * @var string Obsah komentare
	 */
	protected $content;
	
	/**
	 * @ORM\Column(type="datetime", nullable=false, name="create_date")
	 * @var DateTime
	 */
	protected $createDate;

	/**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parentComment")
	 * @var ArrayCollection Comment[]
     */
    protected $replyComments;

    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="replyComments")
     * @ORM\JoinColumn(name="parent_comment_article_id", referencedColumnName="comment_article_id")
     */
    protected $parentComment;

	/**
	 * @param Article $article Odkaz na clanek
	 * @param User $user Odkaz na prihlaseneho uzivatele
	 * @param string $userName Jmeno neprihlaseneho uzivatele
	 * @param string $content Obsah komentare
	 * @param int $parent Nadrazeny prispevek, jedna se tudiz o odpoved na jiny komentar
	 */
	public function __construct($article, $user, $userName, $content, $parent = NULL) {
		$this->article = $article;
		
		if ($user !== NULL) {
			$this->setUser($user);
		}
		if (!empty($userName)) {
			$this->userName = $userName;
		}
		
		$this->content = $content;
		$this->parentComment = $parent;
		$this->createDate = new DateTime();
		$this->replyComments = new ArrayCollection();
	}
	
	/**
	 * @param \App\Model\Entities\User $user
	 * @return \App\Model\Entities\Comment Nastavuje autora - uzivatele komentare
	 */
	public function setUser(User $user) {
		$this->user = $user;
		return $this;
	}
	
	/** @return int comment_article_id pkey */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string Obsah komentare
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * @return DateTime Datum pridani prispevku
	 */
	public function getCreateDate() {
		return $this->createDate;
	}
	
	/**
	 * @return string Jmeno autora komentare
	 */
	public function getAuthorName() {
		if (!empty($this->userName)) {
			return $this->userName;
		} else {
			return $this->user->getId();
		}
	}
	
	/**
	 * @return boolean Ma komentar nejake odpovedi?
	 */
	public function hasReplies() {
		return !$this->replyComments->isEmpty();
	}
	
	/**
	 * @return ArrayCollection Comment[] Odpovedi na komentar
	 */
	public function getReplies() {
		return $this->replyComments;
	}
	
	/**
	 * @return boolean Je komentar odpovedi? Tzn. nema rodice?
	 */
	public function isReply() {
		return !is_null($this->parentComment);
	}
	
}

