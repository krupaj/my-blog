<?php
namespace App\AdminModule\Presenters;

use App\Presenters;

/**
 * Base presenter pro modul Admin
 */
abstract class BaseAdminPresenter extends Presenters\BasePresenter {

	public function beforeRender() {
		if (!$this->getUser()->isLoggedIn() && $this->presenter->getName() != 'Sign') {
			$this->redirect('Sign:in');
		}
	}
}