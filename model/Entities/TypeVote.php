<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Nette\Utils\Strings;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class TypeVote (typ anketni otazky)
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="type_vote")
 */
class TypeVote {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="type_vote_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false, unique=true)
	 * @var string Nazev typu otazky/ankety
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string", nullable=true, name="help_text")
	 * @var string Popisek typu otazky/ankety
	 */
	protected $description;

	/**
	 * @ORM\OneToMany(targetEntity="\App\Model\Entities\Vote", mappedBy="type_vote_id", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "DESC"})
	 * @var ArrayCollection Vote[]
	 */
	protected $votes;
	
	/**
	 * @ORM\Column(type="smallint", name="option_count", nullable=true, unique=false)
	 * @var int Pocet moznych odpovedi na otazku
	 */
	protected $optionCount;
	
	/**
	 * @ORM\Column(type="smallint", name="pick_option_count", nullable=true, unique=false)
	 * @var int Pocet moznych vybranych odpovedi na otazku
	 */
	protected $pickOptionCount;
	
	/**
	 * @ORM\Column(type="boolean", name="open_option", nullable=true, unique=false)
	 * @var boolean Pridat k moznostem otevrenou odpoved?
	 */
	protected $openOption;
	
	/**
	 * @ORM\Column(type="string", name="form_type", nullable=true, unique=false)
	 * @var string Typ formularoveho inputu
	 */
	protected $formType;

	/**
	 * @param string $name
	 * @param string|NULL $description
	 */
	public function __construct($name, $description=NULL) {
		$this->name = $name;
		$this->description = $this->setDescription($description);
		$this->votes = new ArrayCollection();
	}
	
	/**
	 * @return int Identifikator typu ankety, pkey
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string Nazev typu ankety
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = empty($description) ? NULL : $description;
	}
	
	/**
	 * @param int|NULL $length
	 * @return string Napovedny text k vyplneni otazky
	 */
	public function getDescription($length=NULL) {
		if ($length !== NULL) {
			return Strings::truncate($this->description, $length);
		}
		return $this->description;
	}
	
	/**
	 * @return ArrayCollection Ankety asociovane s typem
	 */
	public function getVotes() {
		return $this->votes;
	}
	
	/**
	 * @return int Pocet moznosti (odpovedi) otazky
	 */
	public function getOptionCount() {
		return $this->optionCount;
	}
	
	/**
	 * @return int Pocet vybranych moznosti
	 */
	public function getPickOptionCount() {
		return $this->pickOptionCount;
	}
	
	/**
	 * @return boolean Ma otazka navic otevrenou odpoved?
	 */
	public function hasOpenOption() {
		return $this->openOption;
	}
	
	/**
	 * @return string Nazev formularoveho inputu
	 */
	public function getFormType() {
		return $this->formType;
	}
}

