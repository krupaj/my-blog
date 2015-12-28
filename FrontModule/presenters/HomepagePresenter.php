<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BaseFrontPresenter {
	
	/** @var Model\Repository\ArticleRepository @inject */
	public $articleRepository;
	/** @var Model\Repository\SectionRepository @inject */
	public $sectionRepository;
	/** @var \App\BlogSettings @inject */
	public $blogSettings;
	/** @var \App\Controls\CommentsFactory @inject */
	public $commentsFactory;
	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $entityManager;
	/** @var Model\Entities\Article */
	protected $article;
	/** @var Model\Entities\Section */
	protected $section;
	/** @var \Nette\Utils\Paginator */
	protected $paginator;
	

	public function actionDefault() {
		$this->template->bgImage = "home-bg.jpg";
		$this->template->title = "Muj blog";
		$this->template->description = "Muj pokus o blog v Nette";
		$this->paginator = new \Nette\Utils\Paginator;
		$this->paginator->setItemsPerPage(self::POST_PER_PAGE); // pocet polozek na strance
		$this->paginator->setPage(1); // cislo aktualni stranky
	}
	
	public function renderDefault() {
		$total = $this->articleRepository->countPublishedArticles();
		$limits = [
			'limit' => $this->paginator->getLength(),
			'offset' => $this->paginator->getOffset()
		];
		$this->template->news = $this->articleRepository->findPublishedArticles($limits);
		$this->paginator->setItemCount($total);
		$this->template->paginator = $this->paginator;
		
	}
	
	/**
	 * @param int $page Nacita dalsi clanky
	 */
	public function handleSetPage($page) {
		if ($this->paginator->getPage() != $page) {
			$this->paginator->setPage($page);
			$this->redrawControl('posts');
			$this->redrawControl('pagin');
		}
	}
	
	/********** action & render POST **********/
	
	/**
	 * Detail clanku
	 * @param string $id Identifikator clanku: article_id-webalize_title
	 */
	public function actionPost($id) {
		
		list($parseId, $parseWebTitle) = Model\Entities\Article::parseWebId($id);
		$this->article = $this->articleRepository->getById($parseId);
		if (!is_object($this->article)) {
			$this->flashMessage('Clanek nenalezen', 'danger');
			$this->redirect('default');
		} else {
			//inkrementovat citac pristupu
			$this->article->setCounter();
			//ulozeni zmeny
			$this->entityManager->flush();
		}
			
		$this->template->bgImage = "post-bg.jpg";
		$this->template->title = $this->article->getTitle();
		$this->template->description = $this->article->getDescription(100);
		
	}
	
	public function renderPost() {
		$this->template->article = $this->article;
	}
	
	/**
	 * @return \App\Controls\Comments Vypis komentaru + form na pridani komentu
	 */
	public function createComponentComments() {
		$component = $this->commentsFactory->create($this->article);
		return $component;
	}
	
	/********** action & render SECTION **********/
	
	/**
	 * @param string $id Web title rubriky
	 */
	public function actionSection($id) {
		$this->section = $this->sectionRepository->findSectionByTitle($id);
		if (!is_object($this->section)) {
			$this->flashMessage('Rubrika nenalezena', 'danger');
			$this->redirect('default');
		}
		//\Tracy\Debugger::log($this->section->getArticles(), 'response');
		$this->template->bgImage = "home-bg.jpg";
		$this->template->title = $this->section->getTitle();
		$this->template->description = "Rubrika | Muj blog";
		//nastaveni strankovani
		$this->paginator = new \Nette\Utils\Paginator;
		$this->paginator->setItemsPerPage(self::POST_PER_PAGE);
		$this->paginator->setPage(1);
	}
	
	public function renderSection() {
		/** @var Doctrine\Common\Collections\ArrayCollection */
		$articles = $this->section->getArticles();
		$total = $articles->count();
		$this->template->news = $articles->slice($this->paginator->getOffset(), $this->paginator->getLength());
		$this->paginator->setItemCount($total);
		$this->template->paginator = $this->paginator;
		
	}
	
	
}
