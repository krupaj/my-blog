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
	 * @var string Nazev tagu v podobe pouzitelne pro url
	 */
	protected $webalizeTitle;
	
	/**
	 * @ORM\OneToMany(targetEntity="\App\Model\Entities\Article", mappedBy="section", cascade={"persist"})
	 * @ORM\OrderBy({"publishDate" = "DESC"})
	 * @var ArrayCollection Article[]
	 */
	protected $articles;


	/**
	 * @param type $title
	 */
	public function __construct($title) {
		$this->setTitle($title);
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
	 * @return ArrayCollection Clanky asociovane s rubrikou
	 */
	public function getArticles() {
		return $this->articles;
	}
	
}

