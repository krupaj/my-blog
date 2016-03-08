<?php
namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\Repository\UserRepository;
use App\Forms\BaseFormFactory;
use App\Model\Entities\User;
use Kdyby\Translation\Phrase;

class UserFormFactory extends Nette\Object {
	/** @var \App\Model\Repository\UserRepository */
	private $repository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;
	
	/**
	 * @param UserRepository $repository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(UserRepository $repository, BaseFormFactory $baseFormFactory) {
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
		$this->baseFormFactory = $baseFormFactory;
	}

	/**
	 * @param User Uzivatel k editaci
	 * @return Form 
	 */
	public function create($user = NULL) {
		$form = $this->baseFormFactory->create();
		$form->addText('login', 'system.credentialsName')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$form->addPassword('password', 'system.credentialsPassword');
		$form->addPassword('password2', 'system.credentialsPassword')
			->addConditionOn($form['password'], Form::FILLED)
				->setRequired(new Phrase('system.requiredItem', ['label' => '%label']))
				->addRule(Form::EQUAL, new Phrase('system.equalItem', ['label' => '%label']), $form['password']);
		
		$form->addSelect('role', 'system.userRole')
			->setItems(User::getRoleList());
		
		$form->addSubmit('send', 'system.save');
		
		//id, vychozi hodnoty pri editaci
		$form->addHidden('id');
		if ($user !== NULL) {
			$defaults = $this->getDefaults($user);
			$form->setDefaults($defaults);
		}
		$form->onValidate[] = [$this, 'validateForm'];
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * Validace formulare s uzivatelskymi udaji
	 * @param Form $form
	 */
	public function validateForm(Form $form) {
		$values = $form->getValues();
		
		//zmena hesla
		if (!empty($values->password)) {
			if ($values->password != $values->password2) {
				$form->addError($form->getTranslator()->translate('system.equalItem', ['label' => $form['password']->getLabel()->getText()]));
			}
		}
		
	}
	
	/**
	 * Zpracovani formulare s clankem
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		//novy uzivatel nebo jeho editace
		$result = empty($values->id) ? $this->newUser($values) : $this->editUser($values);
		if ($result) {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestS'), 'success');
		} else {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestN'), 'danger');
		}
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values
	 * @return boolean Vytvoreni noveho uzivatele problehlo uspesne?
	 */
	protected function newUser($values) {
		$result = TRUE;
		try {
			//pridani noveho uzivatele a ulozeni zmen
			$newUser = new User($values->login, $values->password, $values->role);
			
			$this->em->persist($newUser);
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values Hodnoty z formulare
	 * @return boolean Editace uzivatele provedena uspesne?
	 */
	protected function editUser($values) {
		$result = TRUE;
		try {
			/** @var User $editUser */
			$editUser = $this->repository->getUserById($values->id);
			if (!$editUser) {
				return FALSE;
			}
			// nastaveni atributu
			$editUser->setLogin($values->login);
			$editUser->setRole($values->role);
			if (!empty($values->password)) {
				$editUser->setPassword($values->password);
			}
			//ulozeni zmeny
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param User $user
	 * @return array Vychozi hodnoty pro formular
	 */
	protected function getDefaults($user) {
		$result = [];
		$result['id'] = $user->getId();
		$result['login'] = $user->getLogin();
		$result['role'] = $user->getRole();
		
		return $result;
	}

}