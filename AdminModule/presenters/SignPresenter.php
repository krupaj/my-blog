<?php
namespace App\AdminModule\Presenters;

use Nette;
use App\Forms\SignFormFactory;


final class SignPresenter extends \App\Presenters\BasePresenter {
	/** @var SignFormFactory */
	protected $signFactory;

	public function __construct(SignFormFactory $signFactory) {
		$this->signFactory = $signFactory;
	}
	/**
	 * Sign-in form factory na prihlaseni uzivatele.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm() {
		$form = $this->factory->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Dashboard:');
		};
		return $form;
	}

	/**
	 * Odhlaseni uzivatele
	 */
	public function actionOut() {
		$this->getUser()->logout();
		$this->flashMessage($this->translator->translate('system.logoutS'));
		$this->redirect('in');
	}

}
