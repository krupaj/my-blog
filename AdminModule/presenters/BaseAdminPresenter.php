<?php
namespace App\AdminModule\Presenters;

use App\Presenters;

/**
 * Base presenter pro modul Admin
 */
abstract class BaseAdminPresenter extends Presenters\BasePresenter {

	public function beforeRender() {
		//neni-li uzivatel prihlasen, nema pristup do administrace
		if (!$this->getUser()->isLoggedIn() && $this->presenter->getName() != 'Sign') {
			$this->redirect('Sign:in');
		}
	}
}