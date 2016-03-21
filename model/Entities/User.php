<?php
namespace App\Model\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Nette\Security\Passwords;

/**
 * Class User
 * @package App\Model\Entities
 * @author Jiri Krupnik <krupaj@seznam.cz>
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="public.user")
 */
class User {
	use \Kdyby\Doctrine\Entities\MagicAccessors;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="user_id")
	 * @ORM\GeneratedValue
	 * @var int 
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false, unique=true)
	 * @var string Prihlasovaci jmeno
	 */
	protected $login;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Hash hesla
	 */
	protected $password;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var string Role uzivatele
	 */
	protected $role;
	
	/**
     * @ORM\OneToOne(targetEntity="App\Model\Entities\Person", cascade={"persist"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="person_id", nullable=true)
	 * @var Person|NULL
     */
	protected $person;

	/**
	 * @var array Seznam roli 
	 */
	static private $roleList = [
		'admin' => 'admin',
		'moderator' => 'moderator',
		'guest' => 'guest'
	];
	
	/**
	 * @param string $login
	 * @param string $password
	 * @param string $role
	 */
	public function __construct($login, $password, $role = 'guest') {
		$this->login = $login;
		$this->setPassword($password);
		$this->setRole($role);
		
	}
	
	public function setRole($role) {
		if (in_array($role, self::$roleList)) {
			$this->role = $role;
		}
	}
	
	/**
	 * @return string Role uzivatele
	 */
	public function getRole() {
		return $this->role;
	}
	
	/**
	 * @return array $roleList Seznam roli
	 */
	public static function getRoleList() {
		return self::$roleList;
	}
	
	/**
	 * @param string $password Plain text psw
	 */
	public function setPassword($password) {
		$this->password = Passwords::hash($password);
	}
	
	/**
	 * Nastaveni prihlasovaciho jmena
	 * @param string $login
	 */
	public function setLogin($login) {
		if ($this->getLogin() != $login) {
			$this->login = $login;
		}
	}
	
	/**
	 * @return string Hash hesla
	 */
	public function getPassword() {
		return $this->password;
	}
	
	public function getLogin() {
		return $this->login;
	}
	
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param \App\Model\Entities\Person $person
	 */
	public function setPerson(Person $person) {
		$this->person = $person;
	}
}

