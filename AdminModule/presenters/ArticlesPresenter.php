<?php
namespace App\AdminModule\Presenters;

use App\Model;
use App\Forms\ArticleFormFactory;

/**
 * Sprava clanku
 * @author Jiri Krupnik <krupaj@seznam.cz>
 * @package \App\AdminModule\Presenters
 */
final class ArticlesPresenter extends BaseAdminPresenter {
	/** @var ArticleFormFactory @inject */
	public $articleForm;
	/** @var \App\Model\Repository\ArticleRepository @inject */
	public $articleRepository;
	/** @var Model\Entities\Article|NULL */
	protected $myArticle = NULL;

	public function renderDefault() {
	}
	
	/**
	 * Pozadavek pro pridani noveho clanku
	 */
	public function handleNewArticle() {
		$this->template->title = $this->translator->translate('system.newPost');
		$this->template->component = 'manageArticle';
		$this->redrawControl('formContainer');
	}
	
	/**
	 * @param int $articleId
	 * @return void Editace clanku
	 */
	public function handleEditArticle($articleId) {
		$this->myArticle = $this->articleRepository->getById($articleId);
		if (!$this->myArticle) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		
		$this->template->title = $this->translator->translate('system.editPost');
		$this->template->component = 'manageArticle';
		$this->redrawControl('formContainer');
	}
	
	/**
	 * @param int $articleId
	 * @return void Odstraneni clanku
	 */
	public function handleDeleteArticle($articleId) {
		$this->myArticle = $this->articleRepository->getById($articleId);
		if (!$this->myArticle) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		$result = $this->articleRepository->deleteArticle($this->myArticle);
		if ($result) {
			$this->flashMessage($this->translator->translate('system.requestS'), self::MESSAGE_SUCCESS);
		} else {
			$this->flashMessage($this->translator->translate('system.requestN'), self::MESSAGE_DANGER);
		}
		$this->redirect('this');
	}
	
	/**
	 * @return ArticleFormFactory New/edit clanku
	 */
	public function createComponentManageArticle() {
		$form = $this->articleForm->create($this->myArticle);
		$form->setTranslator($this->translator);
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('this');
		};
		return $form;
	}
	
	/**
	 * @return void Odesle odpoved pro DataTables 
	 */
	public function handleProcessArticles() {
		$offset = $this->getParameter('start', 0);
		$limit = $this->getParameter('length', 10);
		$draw = $this->getParameter('draw', 1);
		
		$limits = [
			'limit' => $limit,
			'offset' => $offset
		];
		$total = $this->articleRepository->countAllArticles();
		$myArticles = $this->articleRepository->findAllArticles($limits);
		$articles = $this->parseArticles($myArticles);
		
		$answer = [
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
			'data' => $articles
		];
		$this->sendJson($answer);
	}
	
	/**
	 * 
	 * @param Model\Entities\Article $articles
	 * @return array
	 */
	protected function parseArticles($articles) {
		$result = [];
		foreach ($articles as $article) {
			$editLink = $this->link('editArticle!', ['articleId' => $article->getId()]);
			$deleteLink = $this->link('deleteArticle!', ['articleId' => $article->getId()]);
			$myArticle = [];
			$myArticle['DT_RowAttr'] = [
				'data-editLink' => $editLink,
				'data-deleteLink' => $deleteLink
			];
			$myArticle[] = $article->getPublishDate()->format('d. m. Y, H:i:s');
			$myArticle[] = $article->getTitle();
			$myArticle[] = $article->getSection()->getTitle();
			$myArticle[] = $article->isPublished();
			$myArticle[] = ''; //potreba kvuli tlacitkum
			$result[] = $myArticle;
		}
		return $result;
	}

}