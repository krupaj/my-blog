<?php
namespace App\AdminModule\Presenters;

use App\Model;
use App\Model\Repository\UserRepository;
use App\Forms\UserFormFactory;

/**
 * Sprava uzivatelu
 * @author Jiri Krupnik <krupaj@seznam.cz>
 * @package \App\AdminModule\Presenters
 */
final class UsersPresenter extends BaseAdminPresenter {

	/** @var UserFormFactory @inject */
	public $userForm;
	/** @var UserRepository @inject */
	public $userRepository;
	/** @var Model\Entities\User|NULL */
	private $myUser = NULL;
	
	
	public function __construct() {
		
	}
	
	/**
	 * Prehled uzivatelu, data se plni pozadavkem z datatables
	 */
	public function actionDefault() {
		$this->template->title = $this->translator->translate('system.post', 2);
	}
	
	/**
	 * @param int $userId
	 * @return void Editace clanku
	 */
	public function handleEditUser($userId) {
		$this->redirect('edit', ['userId' => $userId]);
	}
	
	/**
	 * @param int $userId
	 * @return void Odstraneni uzivatele
	 */
	public function handleDeleteUser($userId) {
		//neni-li admin, nemuze mazat ostatni
		if (!$this->user->isInRole('admin')) {
			$this->flashMessage($this->translator->translate('system.requestNA'), self::MESSAGE_DANGER);
			return;
		}
		$this->myUser = $this->userRepository->getUserById($userId);
		if (!$this->myUser) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		
		$result = $this->userRepository->deleteUser($this->myUser);
		if ($result) {
			$this->flashMessage($this->translator->translate('system.requestS'), self::MESSAGE_SUCCESS);
		} else {
			$this->flashMessage($this->translator->translate('system.requestN'), self::MESSAGE_DANGER);
		}
		$this->redirect('this');
	}
	
	
	/**
	 * @return void Odesle odpoved pro DataTables 
	 */
	public function handleProcessUsers() {
		$offset = $this->getParameter('start', 0);
		$limit = $this->getParameter('length', 10);
		$draw = $this->getParameter('draw', 1);
		
		$limits = [
			'limit' => $limit,
			'offset' => $offset
		];
		$total = $this->userRepository->countAllUsers();
		$myUsers = $this->userRepository->findAllUsers($limits);
		$users = $this->parseUsers($myUsers);
		
		$answer = [
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
			'data' => $users
		];
		$this->sendJson($answer);
	}
	
	/**
	 * Prevod uzivatelu do pole (kvuli datatables)
	 * @param Model\Entities\User $users
	 * @return array
	 */
	protected function parseUsers($users) {
		$result = [];
		foreach ($users as $user) {
			$editLink = $this->link('editUser!', ['userId' => $user->getId()]);
			$deleteLink = $this->link('deleteUser!', ['userId' => $user->getId()]);
			
			$myUser = [];
			$myUser['DT_RowAttr'] = [
				'data-editLink' => $editLink,
				'data-deleteLink' => $deleteLink
			];
			$myUser[] = $user->getLogin();
			$myUser[] = $user->getRole();
			
			$myUser[] = ''; //potreba kvuli tlacitkum
			$result[] = $myUser;
		}
		return $result;
	}
	
	/**
	 * @return type New/edit uzivatele
	 */
	protected function createComponentManageUser() {
		$form = $this->userForm->create($this->myUser);
				
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('this');
		};
		return $form;
	}
	
	
	/********** action & render NEW **********/
	
	public function actionNew() {
		//neni-li admin, nemuze pridavat uzivatele
		if (!$this->user->isInRole('admin')) {
			$this->flashMessage($this->translator->translate('system.requestNA'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
		
		$this->template->title = $this->translator->translate('system.userNew');
	}
	
	/********** action & render EDIT **********/
	
	/**
	 * @param int $userId
	 */
	public function actionEdit($userId) {
		//neni-li admin, nemuze editovat ostatni
		if ($this->user->id != $userId && !$this->user->isInRole('admin')) {
			$this->flashMessage($this->translator->translate('system.requestNA'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
	
		$this->myUser = $this->userRepository->getUserById($userId);
		if (!$this->myUser) {
			$this->flashMessage($this->translator->translate('system.invalidId'), self::MESSAGE_DANGER);
			$this->redirect('default');
		}
		$this->template->title = $this->translator->translate('system.userProfile');
	}
	
	public function renderEdit() {
		$this->template->person = $this->myUser->person;
	}

}