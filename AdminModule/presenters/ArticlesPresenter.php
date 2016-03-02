<?php
namespace App\AdminModule\Presenters;

use App\Model;
use App\Forms\ArticleFormFactory;
use App\Forms\VoteFormFactory;

/**
 * Sprava clanku
 * @author Jiri Krupnik <krupaj@seznam.cz>
 * @package \App\AdminModule\Presenters
 */
final class ArticlesPresenter extends BaseAdminPresenter {
	/** @var ArticleFormFactory @inject */
	public $articleForm;
	/** @var VoteFormFactory @inject */
	public $voteArticleForm;
	/** @var \App\Model\Repository\ArticleRepository @inject */
	public $articleRepository;
	/** @var \App\Model\Repository\SectionRepository @inject */
	public $sectionRepository;
	/** @var \App\Model\Repository\TagRepository @inject */
	public $tagRepository;
	/** @var Model\ArticleImageStorage @inject */
	public $imageStorage;
	/** @var Model\Entities\Article|NULL */
	private $myArticle = NULL;
	
	
	public function __construct() {
		
	}

	public function actionDefault() {
		$this->template->title = $this->translator->translate('system.post', 2);
	}
	
	/**
	 * @param int $articleId
	 * @return void Editace clanku
	 */
	public function handleEditArticle($articleId) {
		$this->redirect('edit', ['articleId' => $articleId]);
	}
	
	/**
	 * @param int $articleId
	 * @return void Nahled clanku
	 */
	public function handlePreviewArticle($articleId) {
		$this->redirect(':Front:Homepage:Post', ['id' => $articleId]);
	}
	
	/**
	 * @param int $articleId
	 * @return void Propojeni clanku a ankety
	 */
	public function handleVoteArticle($articleId) {
		$this->myArticle = $this->articleRepository->getById($articleId);
		if (!$this->myArticle) {
			$this->flashMessage($this->translator->translate('system.invalidId'), self::MESSAGE_DANGER);
			return;
		}
		
		$this->template->title = $this->translator->translate('system.vote');
		$this->template->component = 'voteArticle';
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
		$this->imageStorage->deleteArticleImage($this->myArticle);
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
		$sections = $this->getSections();
		$tags = $this->getTags();
		$form = $this->articleForm->create($this->myArticle, $sections, $tags);
		
		$form->onValidate[] = function ($form) {
			if ($form->hasErrors()) {
				$this->template->component = 'manageArticle';
				$this->redrawControl('formContainer');
			}
		};
		
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('this');
		};
		
		return $form;
	}
	
	/**
	 * @return VoteFormFactory Anketa ke clanku
	 */
	public function createComponentVoteArticle() {
		$articleId = isset($this->myArticle) ? $this->myArticle->getId() : NULL;
		$form = $this->voteArticleForm->create($articleId, []);
		$form->onValidate[] = function ($form) {
			if ($form->hasErrors()) {
				$this->template->component = 'voteArticle';
				$this->redrawControl('formContainer');
			}
		};
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('default');
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
			$voteLink = $this->link('voteArticle!', ['articleId' => $article->getId()]);
			$preLink = $this->link('previewArticle!', ['articleId' => $article->getWebId(5)]);
			$myArticle = [];
			$myArticle['DT_RowAttr'] = [
				'data-editLink' => $editLink,
				'data-deleteLink' => $deleteLink,
				'data-voteLink' => $voteLink,
				'data-preLink' => $preLink
			];
			$myArticle[] = $article->getPublishDate()->format('d. m. Y, H:i:s');
			$myArticle[] = $article->getTitle();
			if ($article->getSection() === NULL) {
				$myArticle[] = FALSE;
			} else {
				$myArticle[] = $article->getSection()->getTitle();
			}
			$myArticle[] = $article->isPublished();
			$myArticle[] = ''; //potreba kvuli tlacitkum
			$result[] = $myArticle;
		}
		return $result;
	}
	
	/**
	 * Vraci dostupne sekce
	 * @return array
	 */
	protected function getSections() {
		$result = [];
		$sections = $this->sectionRepository->getAllSections();
		foreach ($sections as $section) {
			/** @var $section Model\Entities\Section */
			$result[$section->getId()] = $section->getTitle();
		}
		return $result;
	}
	
	/**
	 * Vraci dostupne tagy
	 * @return array
	 */
	protected function getTags() {
		$result = [];
		$tags = $this->tagRepository->getAllTags();
		foreach ($tags as $tag) {
			/** @var $tag Model\Entities\Section */
			$result[$tag->getId()] = $tag->getTitle();
		}
		return $result;
	}
	
	/********** action & render NEW **********/
	
	public function actionNew() {
		$this->template->title = $this->translator->translate('system.newPost');
	}
	
	/********** action & render EDIT **********/
	
	/**
	 * @param int $articleId
	 */
	public function actionEdit($articleId) {
		$this->myArticle = $this->articleRepository->getById($articleId);
		if (!$this->myArticle) {
			$this->flashMessage($this->translator->translate('system.invalidId'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
		$this->template->title = $this->translator->translate('system.editPost');
	}
	
	public function renderEdit() {
		
	}

}