<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class ArticleFormFactory extends Nette\Object {
	/** @var \App\Model\Repository\ArticleRepository */
	private $repository;

	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;

	/**
	 * @param \App\Model\Repository\ArticleRepository $repository
	 * @param \Kdyby\Doctrine\EntityManager $em
	 */
	public function __construct(\App\Model\Repository\ArticleRepository $repository, \Kdyby\Doctrine\EntityManager $em) {
		$this->repository = $repository;
		$this->em = $em;
	}


	/**
	 * @return Form
	 */
	public function create($id = NULL) {
		$form = new Form;
		$form->addText('title', 'Title:')
			->setRequired('Please enter title.');

		$form->addText('description', 'Description:')
			->setRequired('Please enter description.');
		
		$form->addCheckbox('published', 'Published');
		
		//chybi publish_date

		$form->addTextArea('content', 'Content:')
				->setRequired('Please enter content');

		$form->addSubmit('send', 'Save');
		
		//id, vychozi hodnoty pri editaci
		$form->addHidden('id');
		if ($id !== NULL) {
			/** @var \App\Model\Entities\Article */
			$article = $this->repository->getById($id);
			$defaults = $this->getDefaults($article);
			$form->setDefaults($defaults);
		}

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * Zpracovani formulare s clankem
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		if (empty($values->id)) {
			//novy clanek	
			$result = $this->newArticle($values);
			if ($result) {
				$form->getPresenter()->flashMessage('Clanek byl uspesne pridan');
			} else {
				$form->getPresenter()->flashMessage('Clanek nebyl pridan');
			}
		} else {
			//editace clanku
			$result = $this->editArticle($values);
			if ($result) {
				$form->getPresenter()->flashMessage('Zmeny ulozeny');
			} else {
				$form->getPresenter()->flashMessage('Zmeny nebyly ulozeny');
			}
		}
		
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values
	 * @return boolean Vytvoreni noveho clanku problehlo uspesne?
	 */
	protected function newArticle($values) {
		$result = TRUE;
		$today = new Nette\Utils\DateTime();
		$values->offsetSet('publishDate', $today);
		try {
			$newArticle = new \App\Model\Entities\Article($values->title, $values->description, $values->content, $values->publishDate, $values->published);
			//pridani noveho clanku
			$this->em->persist($newArticle);
			//ulozeni zmen
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values Hodnoty z formulare
	 * @return boolean Editace provedena uspesne?
	 */
	protected function editArticle($values) {
		$result = TRUE;
		try {
			/** @var \App\Model\Entities\Article */
			$editArticle = $this->repository->getById($values->id);
			if (!$editArticle) {
				return FALSE;
			}
			// nastaveni atributu
			$editArticle->setPublished($values->published);
			$editArticle->setTitle($values->title);
			//ulozeni zmeny
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param \App\Model\Entities\Article $article
	 * @return array Vychozi hodnoty pro formular
	 */
	protected function getDefaults($article) {
		$result = [];
		if (!$article) {
			return $result;
		}
		$result['id'] = $article->getId();
		$result['title'] = $article->getTitle();
		$result['description'] = $article->getDescription();
		$result['published'] = $article->isPublished();
		$result['content'] = $article->getContent();
		return $result;
	}

}
