<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use	Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Option
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="option")
 */
class Option {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="option_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Odpoved v anketni otazce
	 */
	protected $value;
	
	
	/**
	 * @param string $option
	 */
	public function __construct($option) {
		$this->value = $option;
		
	}
	
	/**
	 * @return int option_id pkey
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string Odpoved v anketni otazce
	 */
	public function getOption() {
		
		return $this->question;
	}
	
}

