<?php
namespace App\Model\Repository;

use Nette;
use App\Model\Entities;


/**
 * Section management (rubrika, sekce).
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
     * Vrátí entity manager
     * @return \Kdyby\Doctrine\EntityManager
     */
    public function getEntityManager() {
        return $this->em;
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
	
	/**
	 * Vraci sekci podle Id (section_id)
	 * @param int $id section_id pkey
	 * @return object|null
	 */
	public function getById($id) {
		return $this->repository->find($id);
	}
	
	/**
	 * Odstraneni konkretni rubriky
	 * @param \App\Model\Entities\Section $section
	 * @return boolean
	 */
	public function deleteArticle(Entities\Section $section) {
		try {
			$this->em->remove($section);
			$result = $this->em->flush();
		} catch (\Doctrine\ORM\ORMException $e) {
			Debugger::log($e, Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}

}
