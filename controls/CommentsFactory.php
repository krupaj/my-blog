<?php
namespace App\Controls;

use \App\Model\Entities\Article;
use Nette\Application\UI;
use Nette\Application\UI\Form;

/**
 * Control pro pridani a vypsani komentaru ke clankum
 */
class Comments extends UI\Control {
	const COMMENT_PER_PAGE = 10;
	
	/** @var Article */
	private $article;
	/** @var \Nette\Http\SessionSection */
	private $commentSession;
	/** @var \Kdyby\Translation\Translator */
	private $translator;
	/** @var \App\Model\Repository\ArticleRepository */
	private $articleRepository;
	/** @var User Prihlaseneho uzivatele */
	private $myUser;
	/** @var \Nette\Utils\Paginator */
	private $paginator;

	/**
	 * @param Article $article Clanek, ktery se komentuje
	 * @param \Nette\Http\Session $session Session pro ulozeni hodnot pri nahledu komentare
	 * @param \Kdyby\Translation\Translator $translator Prekladac
	 * @param \App\Model\Repository\ArticleRepository $repository 
	 */
	public function __construct(Article $article, \Nette\Http\Session $session, \Kdyby\Translation\Translator $translator, \App\Model\Repository\ArticleRepository $repository) {
		$this->article = $article;
		$this->commentSession = $session->getSection('comments');
		$this->translator = $translator;
		$this->articleRepository = $repository;
		
		$this->paginator = new \Nette\Utils\Paginator;
		$this->paginator->setItemsPerPage(self::COMMENT_PER_PAGE);
		$this->paginator->setPage(1);
	}
	
	public function render() {
		
		$comments = $this->article->getComments();
		$this->template->comments = $comments->slice($this->paginator->getOffset(), $this->paginator->getLength());
		$this->template->setFile(__DIR__ . '/templates/comments.latte');
		
		$total = count($this->article->getComments());
		$this->paginator->setItemCount($total);
		$this->template->paginator = $this->paginator;
		
		$this->template->render();
	}

	/**
	 * 1. Krok pri pridavani noveho komentare
	 * @return Form Sepsani a nahled, ulozeni do session
	 */
	public function createComponentForm() {
		$form = new Form;
		$form->setRenderer(new \Nextras\Forms\Rendering\Bs3FormRenderer);
		$form->setTranslator($this->translator);
		$form->addText('name', 'system.commentName')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));
		$form->addTextArea('content', 'system.commentContent')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']))
			->setAttribute('rows', 6);

		$form->addHidden('articleId', $this->article->getId());
		$form->addSubmit('preview', 'system.commentPreview');
		//prejit ke druhemu kroku, coz je ulozeni
		$form->onSuccess[] = [$this, 'formPreview'];
		return $form;
	}
	
	/**
	 * Nahled prispevku pro odeslani
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formPreview(Form $form, $values) {
		//ulozeni do session
		$this->commentSession->content = (array) $values;
		
		$this->template->modal = TRUE;
		$this->template->modalContent = $values->content;
		$this->template->modalTitle = $form->getTranslator()->translate('system.commentPreview');
		$this->redrawControl('modal');
	}
	
	/**
	 * 2. Krok pri pradavani noveho komentare
	 * @return Form Nahled pres texy a ulozeni
	 */
	public function createComponentComment() {
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new \Nextras\Forms\Rendering\Bs3FormRenderer);
		//tlacitko na ulozeni komentar, values se nacitaji ze session
		$form->addSubmit('save', 'system.save');
		
		//prejit ke druhemu kroku, coz je ulozeni
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	/**
	 * @param Form $form
	 * @return void Zpracovani formulare - pridani noveho komentare
	 */
	public function formSucceeded(Form $form) {
		//nacteni a smazani session
		$values = \Nette\Utils\ArrayHash::from($this->commentSession->content);
		$this->commentSession->remove();
		
		//ulozit novy prispevek ke clanku
		$this->article = $this->articleRepository->getById($values->articleId);
		if (isset($this->myUser)) {
			$values->user = $this->myUser;
			$values->name = NULL;
		} else {
			$values->user = NULL;
		}
		//vytvoreni komentu, prirazeni ke clanku a ulozeni
		$newComment = new \App\Model\Entities\Comment($this->article, $values->user, $values->name, $values->content);
		$this->articleRepository->getEntityManager()->persist($newComment);
		$this->article->addComment($newComment);
		$this->articleRepository->getEntityManager()->flush();
		
		$this->redirect('this');
	}
	
	/**
	 * Nastavuje stranku paginatoru pro zobrazeni komentaru
	 * @param int $page 
	 */
	public function handleSetCommentPage($page = NULL) {
		if ($page === NULL) {
			$page = $this->paginator->getPage() + 1;
		}
		
		if ($this->paginator->getPage() != $page) {
			$this->paginator->setPage($page);
			$this->redrawControl('comments');
			$this->redrawControl('pagin');
		}
	}
}

interface CommentsFactory {
	/** @return \App\Controls\Comments */
	public function create($article, $session, $translator);
}