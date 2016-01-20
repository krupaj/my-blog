<?php
namespace App\Model\Repository;

use Nette;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use App\Model\Entities;
use \Tracy\Debugger;

/**
 * Articles management.
 * @package App\Model\Repository
 */
class ArticleRepository extends Nette\Object {

	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	
	/** @var \Kdyby\Doctrine\EntityRepository */
	private $myArticleRepository;

	/**
	 * @param \Kdyby\Doctrine\EntityManager $em
	 */
	public function __construct(\Kdyby\Doctrine\EntityManager $em) {
		$this->em = $em;
		$this->myArticleRepository = $em->getRepository(Entities\Article::class);
	}
	
	/**
     * Vrátí entity manager
     * @return \Kdyby\Doctrine\EntityManager
     */
    public function getEntityManager() {
        return $this->em;
    }
	
	/**
	 * @param string $title Webalize nazev clanku
	 * @return Entities\Article|NULL Vraci clanek dle web nazvu
	 */
	public function getByTitle($title) {
		$title = Strings::webalize($title);
		return $this->myArticleRepository->findOneBy(['webalizeTitle' => $title]);
	}
	
	/**
	 * Vraci clanek podle Id (article_id)
	 * @param int $id article_id pkey
	 * @return object|null
	 */
	public function getById($id) {
		return $this->myArticleRepository->find($id);
	}
	
	/**
	 * Odstraneni konkretniho clanku
	 * @param \App\Model\Entities\Article $article
	 * @return boolean
	 */
	public function deleteArticle(Entities\Article $article) {
		try {
			$this->em->remove($article);
			$result = $this->em->flush();
		} catch (\Doctrine\ORM\ORMException $e) {
			Debugger::log($e, Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * Vraci zverejnene clanky
	 * @return \Doctrine\ORM\Query
	 */
	private function createAllArticlesQuery() {
		$query = "SELECT a From \App\Model\Entities\Article a WHERE a.published = true "
				. "and a.publishDate <= :date "
				. "ORDER BY a.publishDate DESC";
		$dql = $this->myArticleRepository->createQuery($query);
		$dql->setParameter('date', new DateTime('now'));
		return $dql;
	}
	
	/**
	 * Pocet zverejnenych clanku
	 * @return \Doctrine\ORM\Query
	 */
	private function createCountAllArticlesQuery() {
		$query = "SELECT COUNT(a.id) FROM \App\Model\Entities\Article a WHERE a.published = true "
				. "AND a.publishDate <= :date ";
		$dql = $this->myArticleRepository->createQuery($query);
		$dql->setParameter('date', new DateTime('now'));
		return $dql;
	}
	
	/**
	 * @return int Pocet publikovanych clanku
	 */
	public function countPublishedArticles() {
		return $this->createCountAllArticlesQuery()->getSingleScalarResult();
	}
	
	/**
	 * @return int Pocet vsech clanku
	 */
	public function countAllArticles() {
		$query = "SELECT COUNT(a.id) FROM \App\Model\Entities\Article a";
		$dql = $this->myArticleRepository->createQuery($query);
		return $dql->getSingleScalarResult();
	}
	
	/**
	 * Vraci publikovane clanky
	 * @param array $limits Poslednich x publikovanych clanku, lenght a offset
	 * @return \Kdyby\Doctrine\ResultSet
	 */
	public function findPublishedArticles($limits = []) {
		if (empty($limits)) {
			return new \Kdyby\Doctrine\ResultSet($this->createAllArticlesQuery());
		} else {
			$query = $this->createAllArticlesQuery()
					->setFirstResult($limits['offset'])
					->setMaxResults($limits['limit']);
			return new \Kdyby\Doctrine\ResultSet($query);
		}
	}
	
	/**
	 * @param array $limits
	 * @return type Vsechny clanky
	 */
	public function findAllArticles($limits = []) {
		if (empty($limits)) {
			return $this->myArticleRepository->findAll();
		}
		return $this->myArticleRepository->findBy([], ['publishDate' => 'DESC'], $limits['limit'], $limits['offset']);
	}
	


}
