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
	/** @var \App\Model\ArticleImageStorage Manipulace s obrazky */
	private $imageStorage;
	
	/**
	 * @param UserRepository $repository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 * @param \App\Model\ArticleImageStorage $imageStorage
	 */
	public function __construct(UserRepository $repository, BaseFormFactory $baseFormFactory, \App\Model\ArticleImageStorage $imageStorage) {
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
		$this->baseFormFactory = $baseFormFactory;
		$this->imageStorage = $imageStorage;
	}

	/**
	 * @param User Uzivatel k editaci
	 * @return Form 
	 */
	public function create($user = NULL) {
		$form = $this->baseFormFactory->create();
		/* UZIVATELSKA CAST */
		$userF = $form->addContainer('user');
		$userF->addText('login', 'system.credentialsName')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$userF->addPassword('password', 'system.credentialsPassword');
		
		$userF->addPassword('password2', 'system.credentialsPassword')
			->addConditionOn($form['user']['password'], Form::FILLED)
				->setRequired(new Phrase('system.requiredItem', ['label' => '%label']))
				->addRule(Form::EQUAL, new Phrase('system.equalItem', ['label' => '%label']), $form['user']['password']);

		$userF->addSelect('role', 'system.userRole')
			->setItems(User::getRoleList());
		
		/* OSOBNI CAST */
		$personF = $form->addContainer('person');
		$personF->addText('name', 'system.userName')
			->setRequired(new Phrase('system.requiredItem', ['label' => '%label']));
		
		$personF->addText('surname', 'system.userSurname');
		
		$personF->addUpload('avatar', 'system.userAvatar')
			->addCondition(Form::FILLED)
				->addRule(Form::IMAGE, $form->getTranslator()->translate('system.formImage', ['item' => '%label']));
		
		/* OBECNA CAST */
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
		$userValues = $values->user;
		$personValues = $values->person;
		//zmena hesla
		if (!empty($userValues->password)) {
			if ($userValues->password != $userValues->password2) {
				$form->addError($form->getTranslator()->translate('system.equalItem', ['label' => $form['user']['password']->getLabel()->getText()]));
			}
		}
		
		if (empty($personValues->name)) {
			$form->addError($form->getTranslator()->translate('system.requiredItem', ['label' => $form['person']['name']->getLabel()->getText()]));
		}
		
		$image = NULL;
		if ($personValues->avatar->isImage()) {
			$image = $personValues->avatar;
		}
		if (!empty($image) && (!$image->isOk() || !$image->isImage())) {
			$item = $form->getTranslator()->translate('system.userAvatar');
			$form->addError($form->getTranslator()->translate('system.formImage', ['item' => $item]));
		}
		
	}
	
	/**
	 * Zpracovani formulare s clankem
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		//nastaveni hodnot
		if (empty($values->person->surname)) {
			$values->person->surname = NULL;
		}
		
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
		$userValues = $values->user;
		$personValues = $values->person;
		try {
			// pridani noveho uzivatele a ulozeni zmen
			$newUser = new User($userValues->login, $userValues->password, $userValues->role);
			$newPerson = new \App\Model\Entities\Person($personValues->name, $personValues->surname);
			if ($personValues->avatar->isImage()) {
				$this->imageStorage->setPersonAvatar($newPerson, $personValues->avatar->toImage());
			}
			$newUser->setPerson($newPerson);
			// ulozeni zmen
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
		$userValues = $values->user;
		$personValues = $values->person;
		try {
			/** @var User $editUser */
			$editUser = $this->repository->getUserById($values->id);
			if (!$editUser) {
				return FALSE;
			}
			// nastaveni atributu
			$editUser->setLogin($userValues->login);
			$editUser->setRole($userValues->role);
			if (!empty($userValues->password)) {
				$editUser->setPassword($userValues->password);
			}
			// osoba
			if ($editUser->person !== NULL) {
				$person = $editUser->person;
				$person->name = $personValues->name;
				$person->surname = $personValues->surname;
			} else {
				$person = new \App\Model\Entities\Person($personValues->name, $personValues->surname);
				$editUser->setPerson($person);
			}
			
			if ($personValues->avatar->isImage()) {
				$this->imageStorage->setPersonAvatar($person, $personValues->avatar->toImage());
			}
			// ulozeni zmeny
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
		$result['user']['login'] = $user->getLogin();
		$result['user']['role'] = $user->getRole();
		$person = $user->person;
		if ($person) {
			$result['person']['name'] = $person->name;
			$result['person']['surname'] = $person->surname;
		}
		return $result;
	}

}