<?php
namespace App\Model\Entities;

use Nette\Utils\Strings;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Class Person
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="person")
 */
class Person {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="person_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Jmeno uzivatele
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|NULL Prijmeni uzivatele
	 */
	protected $surname;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Model\Entities\Image", cascade={"persist"})
	 * @ORM\JoinColumn(name="avatar", referencedColumnName="image_id", nullable=true)
	 * @var Image|NULL 
	 */
	protected $avatar;

	/**
	 * @param string $name
	 * @param string $surname
	 */
	public function __construct($name, $surname = NULL) {
		$this->name = $name;
		$this->surname = $surname;
	}
	
	public function getWebalizeName() {
		return Strings::webalize($this->getFullname());
	}
	
	/**
	 * @return string Cele jmeno
	 */
	public function getFullname() {
		$fullName = $this->name;
		if ($this->surname !== NULL) {
			$fullName .= ' ' . $this->surname;
		}
		return $fullName;
	}
	
	/**
	 * @param \App\Model\Entities\Image $avatar
	 */
	public function setAvatar(Image $avatar = NULL) {
		$this->avatar = $avatar;
	}
	
	/**
	 * Ma uzivatelova osoba nastaven profilovy obrazek (avatar)?
	 * @return boolean
	 */
	public function hasAvatar() {
		return !is_null($this->avatar);
	}
	
}

