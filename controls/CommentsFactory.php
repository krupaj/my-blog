<?php

namespace App\Controls;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Form;


class Comments extends UI\Control {
	/** @var \App\Model\Entities\Article */
	private $article;


	public function __construct($article) {
		$this->article = $article;
	}
	
	public function render() {
		$this->template->comments = $this->article->getComments();
		$this->template->setFile(__DIR__ . '/templates/comments.latte');
		$this->template->render();
	}

	/**
	 * @return Form Pridavani noveho komentare
	 */
	public function createComponentForm() {
		$form = new Form;
		$form->setRenderer(new \Nextras\Forms\Rendering\Bs3FormRenderer);
		$form->addText('name', 'Jmeno:')
			->setRequired('Please enter your name.');
		$form->addTextArea('content', 'Komentar')
			->setRequired();

		$form->addHidden('articleId', $this->article->getId());
		$form->addSubmit('preview', 'Preview');

		$form->onSuccess[] = [$this, 'formPreview'];
		return $form;
	}
	
	/**
	 * Nahled prispevku pro odeslani
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formPreview(Form $form, $values) {
		
		$this->template->modal = TRUE;
		$this->template->modalTitle = 'Title';
		$this->template->modalBody = $values->content;
		$this->redrawControl('modal');
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 * @return void Zpracovani formulare - pridani noveho komentare
	 */
	public function formSucceeded(Form $form, $values) {
		//todo vytvorit novy prispevek
		$this->article = $this->articleRepository->getById($values->articleId);
		
		$newComment = new \App\Model\Entities\Comment(NULL, $values->name, $values->content);
		$this->article->addComment($newComment);
		
		$this->em->flush();
	}

}

interface CommentsFactory {
	/** @return \App\Controls\Comments */
	public function create($article);
}
