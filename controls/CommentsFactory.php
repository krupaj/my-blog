<?php
namespace App\Controls;

use \App\Model\Entities\Article;
use Nette\Application\UI;
use Nette\Application\UI\Form;


class Comments extends UI\Control {
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


	public function __construct(Article $article, \Nette\Http\Session $session, \Kdyby\Translation\Translator $translator, \App\Model\Repository\ArticleRepository $repository) {
		$this->article = $article;
		$this->commentSession = $session->getSection('comments');
		$this->translator = $translator;
		$this->articleRepository = $repository;
	}
	
	public function render() {
		$this->template->comments = $this->article->getComments();
		$this->template->setFile(__DIR__ . '/templates/comments.latte');
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
}

interface CommentsFactory {
	/** @return \App\Controls\Comments */
	public function create($article, $session, $translator);
}