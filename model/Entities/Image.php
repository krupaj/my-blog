<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Class Option
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="image")
 */
class Image {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="image_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Nazev/cesta obrazku
	 */
	protected $image;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|NULL Zdroj obrazku (libovolna forma, retezec)
	 */
	protected $source;
	
	
	/**
	 * @param string $image
	 * @param string $source
	 */
	public function __construct($image, $source = NULL) {
		$this->image = $image;
		$this->source = $source;
	}
	
	/**
	 * @return string Nazev/cesta nahledu obrazu
	 */
	public function getThumbnail() {
		return 'pre_' . $this->image;
	}
	
	/**
	 * @return string Alt obrazku (alternativni popis obsahujici odkaz na zdroj obrazku)
	 */
	public function getAlt() {
		$alt = $this->image;
		if ($this->source !== NULL) {
			$alt .= ', ' . $this->source;
		}
		return $alt;
	}
	
}

