<?php
namespace App\AdminModule\Presenters;

use Nette;
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

	public function renderDefault() {
		
	}
	
	public function handleNewArticle() {
		$this->template->title = 'Nový článek';
		$this->template->component = 'newArticle';
		$this->redrawControl('formContainer');
	}
	
	public function createComponentNewArticle() {
		$form = $this->articleForm->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Dashboard:');
		};
		return $form;
	}
	
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
			$myArticle = [];
			$myArticle[] = $article->getPublishDate();
			$myArticle[] = $article->getTitle();
			$myArticle[] = $article->getSection()->getTitle();
			$myArticle[] = $article->isPublished();
			$result[] = $myArticle;
		}
		return $result;
	}

}