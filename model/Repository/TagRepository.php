<?php
namespace App\Model\Repository;

use Nette;
use App\Model\Entities;


/**
 * Tag management (stitek, sekce, nalepka).
 */
class TagRepository extends Nette\Object {

	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var \Kdyby\Doctrine\EntityRepository */
	private $repository;


	public function __construct(\Kdyby\Doctrine\EntityManager $em) {
		$this->em = $em;
		$this->repository = $em->getRepository(Entities\Tag::class);
	}
	
	/**
     * Vrátí entity manager
     * @return \Kdyby\Doctrine\EntityManager
     */
    public function getEntityManager() {
        return $this->em;
    }
	
	/**
	 * @return Entities\Tag[]
	 */
	public function getAllTags() {
		//$result = $this->repository->findAll();
		$result = $this->repository->findBy([], ['title' => 'ASC']);
		return $result;
	}
	
	/**
	 * Vraci tag podle Id (tag_id)
	 * @param int $id tag_id pkey
	 * @return object|null
	 */
	public function getById($id) {
		return $this->repository->find($id);
	}
	
	/**
	 * Odstraneni konkretniho tagu
	 * @param \App\Model\Entities\Tag $tag
	 * @return boolean
	 */
	public function deleteTag(Entities\Tag $tag) {
		try {
			$this->em->remove($tag);
			$result = $this->em->flush();
		} catch (\Doctrine\ORM\ORMException $e) {
			Debugger::log($e, Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}

}
