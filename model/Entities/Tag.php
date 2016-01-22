<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Nette\Utils\Strings;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class Tag
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="tag")
 */
class Tag {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="tag_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false, unique=true)
	 * @var string Nazev tagu
	 */
	protected $title;
	
	/**
	 * @ORM\Column(type="string", nullable=false, name="webalize_title")
	 * @var string Nazev tagu v podobe pouzitelne pro url
	 */
	protected $webalizeTitle;
	
	/**
	 * @ORM\ManyToMany(targetEntity="\App\Model\Entities\Article", mappedBy="tags", cascade={"persist"})
	 * @var ArrayCollection Article[]
	 */
	protected $articles;

	/**
	 * @param type $title
	 */
	public function __construct($title) {
		$this->setTitle($title);
		//$this->setImportance($importance);
		
		$this->articles = new ArrayCollection();
	}
	
	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Identifikator pro web url ve tvaru: ID-WEBTITLE
	 * @return string 
	 */
	public function getWebId() {
		$myWebIdentifier = $this->getId() . '-' . $this->getWebalizeTitle();
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
	 * @param string $title Nazev tagu
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->webalizeTitle = Strings::webalize($title);
	}
	
	/**
	 * @return string Nazev tagu
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Vraci pocatecni pismeno/znak nazvu tagu
	 * @return string
	 */
	public function getFirstLetter() {
		$smallTitle = Strings::upper($this->title);
		return substr($smallTitle, 0,1);
	}
	
	/**
	 * @return string Nazev tagu vhodny pro url
	 */
	public function getWebalizeTitle() {
		return $this->webalizeTitle;
	}
	
	/**
	 * Vraci otagovane clanky
	 * @return Article[]
	 */
	public function getArticles() {
		return $this->articles;
	}
	
	/**
	 * Vraci pocet otagovanych clanku
	 * @return int
	 */
	public function getCountArticles() {
		return $this->articles->count();
	}
	
}