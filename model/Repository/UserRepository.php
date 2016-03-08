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
	 * @return \Kdyby\Doctrine\EntityManager
	 */
	public function getEntityManager() {
		return $this->em;
	}
	
	/**
	 * Vraci uzivatele podle loginu
	 * @param string $login Uzivatelske jmeno
	 * @return Entities\User|null
	 */
	public function getUserByLogin($login) {
		return $this->repository->findOneBy(['login' => $login]);
	}
	
	/**
	 * Vraci uzivatele podle ID
	 * @param int $id user_id
	 * @return Entities\User
	 */
	public function getUserById($id) {
		return $this->repository->findOneBy(['id' => $id]);
	}
	
	/**
	 * Odstraneni uzivatele
	 * @param \App\Model\Entities\User $user
	 */
	public function deleteUser(Entities\User $user) {
		$this->em->remove($user);
		$this->em->flush();
	}
	
	/**
	 * @return int Pocet vsech uzivatelu
	 */
	public function countAllUsers() {
		$query = "SELECT COUNT(a.id) FROM \App\Model\Entities\User a";
		$dql = $this->repository->createQuery($query);
		return $dql->getSingleScalarResult();
	}
	
	/**
	 * Vraci vsechny uzivatele systemu
	 * @param array $limits SQL limit a offset
	 * @return Entities\User[] 
	 */
	public function findAllUsers($limits = []) {
		if (empty($limits)) {
			$limits['offset'] = NULL;
			$limits['limit'] = NULL;
		}
		return $this->repository->findBy([], ['id' => 'DESC'], $limits['limit'], $limits['offset']);
	}

}
