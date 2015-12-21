<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator {

	/** @var Repository\UserRepository */
	private $userRepository;


	public function __construct(\App\Model\Repository\UserRepository $repository) {
		$this->userRepository = $repository;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$user = $this->userRepository->getUserByLogin($username);

		if (!$user) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif (!Passwords::verify($password, $user->getPassword())) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		} 
		$oParams = [
			'login' => $user->getLogin()
		];
		return new Nette\Security\Identity($user->getId(), $user->getRole(), $oParams);
	}

}



class DuplicateNameException extends \Exception
{}
