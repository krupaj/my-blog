<?php

namespace App\Model\Repository;

use Nette;
use App\Model\Entities;


/**
 * Users management.
 */
class UserRepository extends Nette\Object {


	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var \Kdyby\Doctrine\EntityRepository */
	private $repository;


	public function __construct(\Kdyby\Doctrine\EntityManager $em) {
		$this->em = $em;
		$this->repository = $em->getRepository(Entities\User::class);
	}
	
	/**
	 * @param string $login Uzivatelske jmeno
	 * @return Entities\User|null
	 */
	public function getUserByLogin($login) {
		return $this->repository->findOneBy(['login' => $login]);
	}
	
	public function getUserById($id) {
		return $this->repository->findOneBy(['id' => $id]);
	}

}
