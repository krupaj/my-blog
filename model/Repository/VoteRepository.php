<?php
namespace App\Model\Repository;

use Nette;
use App\Model\Entities;
use \Tracy\Debugger;

/**
 * Articles management.
 * @package App\Model\Repository
 */
class VoteRepository extends Nette\Object {

	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	
	/** @var \Kdyby\Doctrine\EntityRepository */
	private $myVoteRepository;

	/**
	 * @param \Kdyby\Doctrine\EntityManager $em
	 */
	public function __construct(\Kdyby\Doctrine\EntityManager $em) {
		$this->em = $em;
		$this->myVoteRepository = $em->getRepository(Entities\Vote::class);
	}
	
	/**
     * Vrátí entity manager
     * @return \Kdyby\Doctrine\EntityManager
     */
    public function getEntityManager() {
        return $this->em;
    }
	
	/**
	 * Vraci ankety dle kriterii: limit, offset
	 * @param array $limits
	 * @return Entities\Vote[]
	 */
	public function findAllVotes($limits = []) {
		if (empty($limits)) {
			return $this->myVoteRepository->findAll();
		}
		return $this->myVoteRepository->findBy([], ['id' => 'DESC'], $limits['limit'], $limits['offset']);
	}

	/**
	 * @return int Pocet vsech anket
	 */
	public function countAllVotes() {
		$query = "SELECT COUNT(v.id) FROM \App\Model\Entities\Vote v";
		$dql = $this->myVoteRepository->createQuery($query);
		return $dql->getSingleScalarResult();
	}
	
	/**
	 * Vraci anketu podle Id (vote_id)
	 * @param int $id vote_id pkey
	 * @return Entities\Vote|NULL
	 */
	public function getById($id) {
		return $this->myVoteRepository->find($id);
	}
	
	/**
	 * Odstraneni konkretni ankety, vcetne odpovedi
	 * @param \App\Model\Entities\Vote $vote
	 * @return boolean
	 */
	public function deleteVote(Entities\Vote $vote) {
		try {
			$options = $vote->getOptions();
			//odstraneni ankety/otazky
			$this->em->remove($vote);
			//odstraneni odpovedi otazky
			foreach ($options as $option) {
				if ($option !== NULL) {
					$this->em->remove($option);
				}
			}
			//provedeni zmen
			$result = $this->em->flush();
		} catch (\Doctrine\ORM\ORMException $e) {
			Debugger::log($e, Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * Vraci vsechny typy anket (anketnich otazek)
	 * @return Entities\TypeVote[]
	 */
	public function findAllVoteTypes() {
		$repository = $this->em->getRepository(Entities\TypeVote::class);
		
		return $repository->findAll();
	}

}
