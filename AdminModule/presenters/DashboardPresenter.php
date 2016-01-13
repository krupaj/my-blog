<?php
namespace App\AdminModule\Presenters;

use App\Model,
	App\Forms\ArticleFormFactory;


final class DashboardPresenter extends BaseAdminPresenter {
	/** @var ArticleFormFactory @inject */
	public $articleForm;

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

}
