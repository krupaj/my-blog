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
	
	public function getId() {
		return $this->id;
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
	 * @return string Nazev tagu vhodny pro url
	 */
	public function getWebalizeTitle() {
		return $this->webalizeTitle;
	}
	
}

