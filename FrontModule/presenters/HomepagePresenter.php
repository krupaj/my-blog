<?php
namespace App\FrontModule\Presenters;

use App\Model;


final class HomepagePresenter extends BaseFrontPresenter {
	
	/** @var Model\Repository\ArticleRepository @inject */
	public $articleRepository;
	/** @var Model\Repository\SectionRepository @inject */
	public $sectionRepository;
	/** @var Model\Repository\TagRepository @inject */
	public $tagRepository;
	/** @var \App\BlogSettings @inject */
	public $blogSettings;
	/** @var \App\Controls\CommentsFactory @inject */
	public $commentsFactory;
	/** @var \App\Controls\IPollFactory @inject */
	public $pollFactory;
	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $entityManager;
	/** @var Model\Entities\Article */
	protected $article;
	/** @var Model\Entities\Section|Model\Entities\Tag */
	protected $objectWithArticles;
	/** @var \Nette\Utils\Paginator */
	protected $paginator;
	

	public function actionDefault() {
		$this->template->bgImage = "home-bg.jpg";
		//title a description se nastavuji defaultne z system.neon
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
		
		$this->template->dPosts = $this->articleRepository->getMostDiscussedArticles();
		$this->template->rPosts = $this->articleRepository->getMostReadedArticles();
		$this->template->nPosts = $this->articleRepository->getRandArticles();
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
	 * @todo Vylepsit to inkrementovani
	 * Detail clanku
	 * @param string $id Identifikator clanku: article_id-webalize_title
	 */
	public function actionPost($id) {
		list($parseId, $parseWebTitle) = Model\Entities\Article::parseWebId($id);
		$this->article = $this->articleRepository->getById($parseId);
		if (!is_object($this->article)) {
			//nepodarilo se ziskat clanek
			$this->flashMessage($this->translator->translate('system.articleNF'), self::MESSAGE_DANGER);
			$this->redirect('default');
		} 
		if (!$this->user->isLoggedIn() && !$this->article->isPublished()) {
			//nezobrazovat nezverejnene clanky neprihlasenym uzivatelum
			$this->flashMessage($this->translator->translate('system.requestNA'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
		
		//inkrementovat citac pristupu a ulozeni zmeny
		$this->article->setCounter();
		$this->entityManager->flush();
	}
	
	public function renderPost() {
		$this->template->article = $this->article;
		$this->template->votes = $this->article->getVotes();
	}
	
	/**
	 * @return \App\Controls\Comments Vypis komentaru + form na pridani komentu
	 */
	public function createComponentComments() {
		$component = $this->commentsFactory->create($this->article, $this->getSession(), $this->translator);
		return $component;
	}
	
	/**
	 * Form pro hlasovani v ankete
	 * @param int $voteId
	 * @return \Nette\Application\UI\Multiplier
	 */
	public function createComponentVote() {
		return new \Nette\Application\UI\Multiplier(function($voteId) {
			$component = $this->pollFactory->create();
			$component->setVote($voteId);
			return $component;
		});
	}
	
	/********** action & render SECTION **********/
	
	/**
	 * @param string $id Web title rubriky
	 */
	public function actionSection($id) {
		/** @var Model\Entities\Section $this->objectWithArticles */
		$this->objectWithArticles = $this->sectionRepository->findSectionByTitle($id);
		if (!is_object($this->objectWithArticles)) {
			$this->flashMessage($this->translator->translate('system.sectionNF'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
		$this->template->bgImage = "home-bg.jpg";
		$this->template->title = $this->objectWithArticles->getTitle();
		$this->template->description = $this->objectWithArticles->getDescription(200);
		//nastaveni zajimavych clanku sekce
		$this->template->dPosts = $this->articleRepository->getMostDiscussedArticles(1, $this->objectWithArticles->getId());
		$this->template->rPosts = $this->articleRepository->getMostReadedArticles(1, $this->objectWithArticles->getId());
		$this->template->nPosts = $this->articleRepository->getRandArticles(1, $this->objectWithArticles->getId());
		
		//nastaveni strankovani
		$this->paginator = new \Nette\Utils\Paginator;
		$this->paginator->setItemsPerPage(self::POST_PER_PAGE);
		$this->paginator->setPage(1);
		$this->setView('articles');
	}
	
	public function renderArticles() {
		/** @var Doctrine\Common\Collections\ArrayCollection */
		$articles = $this->objectWithArticles->getArticles();
		$total = $articles->count();
		$this->template->news = $articles->slice($this->paginator->getOffset(), $this->paginator->getLength());
		$this->paginator->setItemCount($total);
		$this->template->paginator = $this->paginator;
		
	}
	
	/********** action & render TAG **********/
	
	/**
	 * @param string $id Web title tagu
	 */
	public function actionTag($id) {
		list($webId, $webTitle) = Model\Entities\Tag::parseWebId($id);
		/** @var Model\Entities\Tag $this->objectWithArticles */
		$this->objectWithArticles = $this->tagRepository->getById($webId);
		if (!is_object($this->objectWithArticles)) {
			$this->flashMessage($this->translator->translate('system.tagNF'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
		$this->template->bgImage = "home-bg.jpg";
		$this->template->title = $this->objectWithArticles->getTitle();
		$this->template->description = $this->translator->translate('system.tag', 1);
		//nastaveni zajimavych clanku sekce
		$this->template->dPosts = $this->articleRepository->getMostDiscussedArticles();
		$this->template->rPosts = $this->articleRepository->getMostReadedArticles();
		$this->template->nPosts = $this->articleRepository->getRandArticles();
		//nastaveni strankovani
		$this->paginator = new \Nette\Utils\Paginator;
		$this->paginator->setItemsPerPage(self::POST_PER_PAGE);
		$this->paginator->setPage(1);
		$this->setView('articles');
	}
	
	/********** action & render TERMS **********/
	public function renderTerms() {
		$this->template->bgImage = "home-bg.jpg";
		$this->template->description = $this->translator->translate('system.termsHeading');
	}
	
	/********** action & render TERMS **********/
	public function renderProject() {
		$this->template->bgImage = "home-bg.jpg";
		$this->template->description = NULL;
	}
	
	
}