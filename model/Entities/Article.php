<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Nette\Utils\Strings;
use	Nette\Utils\DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Article
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class Article {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="article_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Nazev clanku
	 */
	protected $title;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Popis clanku (subtitle)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="string", nullable=false, name="webalize_title")
	 * @var string Nazev clanku v url formatu
	 */
	protected $webalizeTitle;
	
	/**
	 * @ORM\Column(type="datetime", nullable=false, name="publish_date")
	 * @var DateTime Datum zverejneni clanku
	 */
	protected $publishDate;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true, name="update_date")
	 * @var DateTime|NULL Datum posledni aktualizace clanku 
	 */
	protected $updateDate;

	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default":FALSE})
	 * @var boolean Je clanek uverejnen?
	 */
	protected $published;
	
	/**
	 * @ORM\Column(type="integer", nullable=false)
	 * @var int Poce zobrazeni clanku 
	 */
	protected $counter = 1;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Model\Entities\Section", inversedBy="articles")
	 * @ORM\JoinColumn(name="section_id", referencedColumnName="section_id")
	 * @var Section 
	 */
	protected $section;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @var string Obsah samotneho clanku - cely text
	 */
	protected $content;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Model\Entities\Tag", inversedBy="articles", cascade={"persist"})
	 * @ORM\JoinTable(
	 *	name="article_tag",
	 *	joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="article_id", nullable=false)},
	 *	inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="tag_id", nullable=false)}
	 * )
	 * @var ArrayCollection Tag[]
	 */
	private $tags;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Model\Entities\Comment", mappedBy="article")
	 * @ORM\OrderBy({"createDate" = "DESC"})
	 * @var ArrayCollection Comment[]
	 */
	private $comments;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Model\Entities\Vote", inversedBy="articles", cascade={"persist"})
	 * @ORM\JoinTable(name="article_vote",
     *      joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="article_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="vote_id", referencedColumnName="vote_id")}
     *      )
	 * @var ArrayCollection Vote[]
	 */
	private $votes;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string Nazev obrazku
	 */
	protected $image;


	/**
	 * @param string $title
	 * @param string $content
	 * @param DateTime $publishDate
	 * @param bool $published
	 */
	public function __construct($title, $description, $content, $publishDate, $published = FALSE) {
		$this->tags = new ArrayCollection();
		$this->comments = new ArrayCollection();
		$this->votes = new ArrayCollection();
		$this->description = $description;
		$this->setTitle($title);
		$this->content = $content;
		$this->setPublishDate($publishDate);
		$this->setPublished($published);
	}
	
	/**
	 * @return int article_id pkey
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Identifikator pro web url ve tvaru: ID-WEBTITLE
	 * @return string 
	 */
	public function getWebId() {
		$myWebIdentifier = $this->getId() . '-' . $this->getWebalizeTitle(50);
		return $myWebIdentifier;
	}
	
	public static function parseWebId($webId) {
		$myId = NULL;
		$myWebTitle = NULL;
		$splits = explode('-', $webId, 2);
		
		if (isset($splits[0])) {
			$myId = $splits[0];
		}
		if (isset($splits[1])) {
			$myWebTitle = $splits[1];
		}
		return [$myId, $myWebTitle];
	}

	/**
	 * @param string $title
	 * @return \App\Model\Entities\Article
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->webalizeTitle = Strings::webalize($title);
		return $this;
	}
	
	/**
	 * @return string Nazev clanku (title)
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @param int $maxLength
	 * @return string Popis clanku (subtitle, uvodnik)
	 */
	public function getDescription($maxLength=NULL) {
		if ($maxLength !== NULL) {
			return Strings::truncate($this->description, $maxLength);
		}
		return $this->description;
	}
	
	/**
	 * @param string $description Popis clanku (subtitle, uvodnik)
	 * @return Article
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	/**
	 * @param string|NULL $image
	 * @return boolean Probehlo ulozeni obrazku vcetne nahledu v poradku
	 */
	public function setImage($image = NULL) {
		$this->image = $image;
	}
	
	/**
	 * @todo Neni jako polozka ve formech
	 * @return string Nazev/cesta k hlavnimu obrazku clanku
	 */
	public function getImage() {
		return (is_null($this->image)) ? 'post-bg.jpg' : $this->image;
	}
	
	/**
	 * @return boolean Ma clanek prirazen obrazek?
	 */
	public function hasImage() {
		return !is_null($this->image);
	}
	
	/**
	 * @return string Nazev/cesta nahledu obrazu
	 */
	public function getImageThumbnail() {
		$imageName = $this->getImage();
		return 'pre_' . $imageName;
	}
	
	/**
	 * @param DateTime $date Datum zverejneni clanku
	 */
	public function setPublishDate(DateTime $date) {
		$this->publishDate = $date;
	}
	
	/**
	 * @return DateTime Datum zverejneni clanku
	 */
	public function getPublishDate() {
		return $this->publishDate;
	}
	
	/**
	 * @return DateTime|NULL Datum aktualizace clanku
	 */
	public function getUpdateDate() {
		return $this->updateDate;
	}
	
	/**
	 * @param DateTime $date
	 * @return void Nastavuje datum aktualizace
	 */
	public function setUpdateDate($date = NULL) {
		if ($date === NULL) {
			$date = new DateTime();
		}
		$this->updateDate = $date;
	}
	
	/**
	 * @return boolean Je clanek aktualizovany?
	 */
	public function isUpdated() {
		return !is_null($this->getUpdateDate());
	}
	
	/**
	 * @param int $maxLength
	 * @return string Nazev clanku vhodny pro url
	 */
	public function getWebalizeTitle($maxLength) {
		if ($maxLength !== NULL) {
			return Strings::truncate($this->webalizeTitle, $maxLength, '');
		}
		return $this->webalizeTitle;
	}
	
	/**
	 * @return bool Je clanek urcen ke zverejneni?
	 */
	public function isPublished() {
		return $this->published;
	}
	
	/**
	 * @param bool $published
	 * @return Article
	 */
	public function setPublished($published = FALSE) {
		if (is_bool($published)) {
			$this->published = $published;
		} else {
			$this->published = boolval($published);
		}
		return $this;
	}
	
	/**
	 * @return int Pocet zobrazeni clanku
	 */
	public function getCounter() {
		return $this->counter;
	}
	
	/**
	 * Nastavuje citac pristupu, zvysuje jej o jednicku
	 * @param int $counter
	 * @return Article
	 */
	public function setCounter($counter=NULL) {
		if ($counter === NULL) {
			$this->counter = $this->counter + 1;
		} else {
			$this->counter = $counter;
		}
		return $this;
	}
	
	/**
	 * @return string Obsah clanku ve formatu texy
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * @param string $content Obsah clanku, ve formatu texy
	 */
	public function setContent($content) {
		if (!empty($content)) {
			$this->content = $content;
		}
	}
	
	/**
	 * @return Section
	 */
	public function getSection() {
		return $this->section;
	}
	
	/**
	 * @param Section $section
	 */
	public function setSection(Section $section) {
		$this->section = $section;
	}
	
	/**
	 * Vraci vsechny komentare clanku
	 * @return ArrayCollection Comment[]
	 */
	public function getComments() {
		return $this->comments;
	}
	
	/**
	 * Pridava komentar ke clanku
	 * @param Comment $comment
	 * @return \App\Model\Entities\Article 
	 */
	public function addComment(Comment $comment) {
		if (!$this->comments->contains($comment)) {
			$this->comments->add($comment);
		}
		return $this;
	}
	
	/**
	 * Odstranuje komentar clanku
	 * @param Comment $comment
	 * @return \App\Model\Entities\Article 
	 */
	public function removeComment(Comment $comment) {
		if ($this->comments->contains($comment)) {
			$this->comments->removeElement($comment);
		}
		return $this;
	}
	
	/**
	 * @return boolean Ma clanek prirazene tagy?
	 */
	public function hasTags() {
		return !$this->tags->isEmpty();
	}
	
	
	/**
	 * @return ArrayCollection Tagy clanku
	 */
	public function getTags() {
		return $this->tags;
	}
	
	/**
	 * Pridava tag ke clanku
	 * @param \App\Model\Entities\Tag $tag
	 * @return \App\Model\Entities\Article
	 */
	public function addTag(Tag $tag) {
		if (!$this->tags->contains($tag)) {
			$this->tags->add($tag);
		}
		return $this;
	}
	
	/**
	 * Odebira tag od clanku
	 * @param \App\Model\Entities\Tag $tag
	 * @return \App\Model\Entities\Article
	 */
	public function removeTag(Tag $tag) {
		if ($this->tags->contains($tag)) {
			$this->tags->removeElement($tag);
		}	
		return $this;
	}
	
	/**
	 * Nastavuje tagy
	 * @param Tag[] $tags
	 */
	public function setTags($tags) {
		foreach ($this->tags as $tag) {
			$tagId = $tag->getId();
			if (!isset($tags[$tagId])) {
				//odstranit
				$this->removeTag($tag);
			} else {
				//tag je jiz prirazen
				unset($tags[$tagId]);
			}
		}
		//pridat vsechny nove zbyvajici
		foreach ($tags as $tag) {
			$this->addTag($tag);	
		}
	}
	
	/**
	 * Vraci ankety spojene se clankem
	 * @return Vote[]
	 */
	public function getVotes() {
		if ($this->votes === NULL) {
			$this->votes = new ArrayCollection();
		}
		return $this->votes;
	}
}

