<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Nette\Utils\Strings;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class Section (sekce, rubrika)
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="section")
 */
class Section {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="section_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false, unique=true)
	 * @var string Nazev sekce/rubriky
	 */
	protected $title;
	
	/**
	 * @ORM\Column(type="string", nullable=false, name="webalize_title")
	 * @var string Nazev sekce v podobe pouzitelne pro url
	 */
	protected $webalizeTitle;
	
	/**
	 * @ORM\Column(type="string", nullable=true, name="description")
	 * @var string Popisek rubriky
	 */
	protected $description;

	/**
	 * @ORM\OneToMany(targetEntity="\App\Model\Entities\Article", mappedBy="section", cascade={"persist"})
	 * @ORM\OrderBy({"publishDate" = "DESC"})
	 * @var ArrayCollection Article[]
	 */
	protected $articles;


	/**
	 * @param string $title
	 * @param string|NULL $description
	 */
	public function __construct($title, $description=NULL) {
		$this->setTitle($title);
		$this->description = $description;
		$this->articles = new ArrayCollection();
	}
	
	/**
	 * @return int Identifikator sekce, pkey
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param string $title Nazev sekce
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->webalizeTitle = Strings::webalize($title);
	}
	
	/**
	 * @return string Nazev sekce/rubriky
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @return string Nazev sekce/rubriky vhodny pro url
	 */
	public function getWebalizeTitle() {
		return $this->webalizeTitle;
	}
	
	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = empty($description) ? NULL : $description;
	}
	
	/**
	 * @param type $length
	 * @return string
	 */
	public function getDescription($length=NULL) {
		if ($length !== NULL) {
			return Strings::truncate($this->description, $length);
		}
		return $this->description;
	}
	
	/**
	 * @return ArrayCollection Clanky asociovane s rubrikou
	 */
	public function getArticles() {
		return $this->articles;
	}
	
}

