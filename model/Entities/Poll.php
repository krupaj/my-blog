<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use	Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * Class Vote
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="poll")
 */
class Poll {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="poll_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Model\Entities\Option")
	 * @ORM\JoinColumn(name="option_id", referencedColumnName="option_id")
	 * @var Option
	 */
	protected $option;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Hlasovani v ankete (odpoved)
	 */
	protected $value;
	
	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @var DateTime Datum hlasovani
	 */
	protected $time;
	
	/**
	 * @ORM\Column(type="string", name="voter_identification", nullable=false)
	 * @var string Jednoznacna identifikace v ramci jednoho hlasovani 
	 */
	protected $voterIdentification;


	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string Ip adresa hlasujiciho
	 */
	protected $ip;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string Identifikace prohlizece hlasujiciho
	 */
	protected $agent;
	
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string Cookie retezec hlasujiciho 
	 */
	protected $cookie;

	/**
	 * @param Option $option Volba pro kterou se hlasuje
	 * @param string $value Hodnota hlasovani
	 */
	public function __construct(Option $option, $value) {
		$this->option = $option;
		$this->value = $value;
		$this->time = new DateTime();
	}
	
	/**
	 * Nastavuje identifikator pro jedno hlasovani
	 * @param string $voter_id
	 */
	public function setVoterIdentification($voter_id) {
		$this->voterIdentification = $voter_id;
	}
	
	/**
	 * Nastavuje IP adresu hlasujiciho
	 * @param string $ip
	 */
	public function setIp($ip) {
		$this->ip = $ip;
	}
	
	/**
	 * Nastavuje identifikator prohlizece hlasujiciho
	 * @param string $agent
	 */
	public function setAgent($agent) {
		$this->agent = $agent;
	}
	
	/**
	 * Nastavuje identifikator z cookies hlasujiciho
	 * @param string $cookie
	 */
	public function setCookie($cookie) {
		$this->cookie = $cookie;
	}
	
	/**
	 * @return int Id hlasovani, poll_id pkey
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return DateTime Cas hlasovani
	 */
	public function getTime() {
		return $this->time;
	}
	
	/**
	 * @return string Cookie id hlasujiciho
	 */
	public function getCookie() {
		return $this->cookie;
	}
	
	public function getVoteIdentification() {
		return $this->voterIdentification;
	}
	
	/**
	 * @return string Jednoznacny retezec pro identifikaci jednoho hlasovani
	 */
	public static function generateVoteIdentificator() {
		$now = new DateTime();
		$rand = Random::generate();
		return $now->format('d.m.Y,H:i:s') . 'rand' . $rand;
	}
	
}

