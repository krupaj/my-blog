<?php

namespace App\Model\Repository;

use Nette;
use App\Model\Entities;


/**
 * Section management.
 */
class SectionRepository extends Nette\Object {


	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var \Kdyby\Doctrine\EntityRepository */
	private $repository;


	public function __construct(\Kdyby\Doctrine\EntityManager $em) {
		$this->em = $em;
		$this->repository = $em->getRepository(Entities\Section::class);
	}
	
	/**
	 * 
	 * @return 
	 */
	public function getAllSections() {
		$result = $this->repository->findAll();
		return $result;
	}
	
	/**
	 * @param string $webTitle
	 * @return Entities\Section|NULL Vraci sekci/rubriku dle web titlu (webalize nazev)
	 */
	public function findSectionByTitle($webTitle) {
		return $this->repository->findOneBy(['webalizeTitle' => $webTitle]);
	}

}
