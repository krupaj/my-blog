<?php
namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignFormFactory extends Nette\Object {
	/** @var User */
	private $user;
	/** @var BaseFormFactory */
	private $baseFormFactory;

	public function __construct(User $user, \App\Forms\BaseFormFactory $baseFormFactory) {
		$this->user = $user;
		$this->baseFormFactory = $baseFormFactory;
	}

	/**
	 * @return Form
	 */
	public function create() {
		$form = $this->baseFormFactory->create();
		$form->addText('username', 'system.credentialsName')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']))
			->setAttribute('autofocus', 'autofocus');

		$form->addPassword('password', 'system.credentialsPassword')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$form->addCheckbox('remember', 'system.keepCredentials');

		$form->addSubmit('send', 'system.signIn');

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	/**
	 * Prihlaseni uzivatele
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		if ($values->remember) {
			$this->user->setExpiration('14 days', FALSE);
		} else {
			$this->user->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->user->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($form->getTranslator()->translate('system.credentialsLogError'));
		}
	}

}