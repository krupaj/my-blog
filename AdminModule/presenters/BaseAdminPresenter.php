<?php

namespace App\AdminModule\Presenters;

use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BaseAdminPresenter extends Nette\Application\UI\Presenter
{

	public function beforeRender() {
		if (!$this->getUser()->isLoggedIn() && $this->presenter->getName() != 'Sign') {
			$this->redirect('Sign:in');
		}
	}
}
