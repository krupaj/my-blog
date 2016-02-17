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
	 * @return string
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
	
}

