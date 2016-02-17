<?php
namespace App\AdminModule\Presenters;

use App\Model;
use App\Forms\VoteFormFactory;

/**
 * Sprava anket
 * @author Jiri Krupnik <krupaj@seznam.cz>
 * @package \App\AdminModule\Presenters
 */
final class VotesPresenter extends BaseAdminPresenter {
	/** @var VoteFormFactory @inject */
	public $voteForm;
	/** @var \App\Model\Repository\VoteRepository @inject */
	public $voteRepository;
	/** @var Model\Entities\Vote */
	private $myVote;
	/** Model\Entities\TypeVote[] */
	private $voteTypes;	
	
	public function __construct() {
		
	}

	public function renderDefault() {
		
	}
	
	public function handleEditVote($voteId) {
		$this->redirect('Votes:edit', ['voteId' => $voteId]);
	}
	
	/**
	 * @param int $voteId
	 * @return void Odstraneni ankety
	 */
	public function handleDeleteVote($voteId) {
		$this->myVote = $this->voteRepository->getById($voteId);
		if (!$this->myVote) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		
		$result = $this->voteRepository->deleteVote($this->myVote);
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
	public function handleProcessVotes() {
		$offset = $this->getParameter('start', 0);
		$limit = $this->getParameter('length', 10);
		$draw = $this->getParameter('draw', 1);
		
		$limits = [
			'limit' => $limit,
			'offset' => $offset
		];
		$total = $this->voteRepository->countAllVotes();
		$myVotes = $this->voteRepository->findAllVotes($limits);
		$votes = $this->parseVotes($myVotes);
		
		$answer = [
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
			'data' => $votes
		];
		$this->sendJson($answer);
	}
	
	/**
	 * Prevod anket do pole kvuli DataTables
	 * @param Model\Entities\Vote[] $votes
	 * @return array
	 */
	protected function parseVotes($votes) {
		$result = [];
		foreach ($votes as $vote) {
			$editLink = $this->link('editVote!', ['voteId' => $vote->getId()]);
			$deleteLink = $this->link('deleteVote!', ['voteId' => $vote->getId()]);
			
			$myVote = [];
			$myVote['DT_RowAttr'] = [
				'data-editLink' => $editLink,
				'data-deleteLink' => $deleteLink,
			];
			$myVote[] = $vote->getQuestion(100);
			$myVote[] = $vote->getTypeVote()->getName();
			$myVote[] = ''; //potreba kvuli tlacitkum
			$result[] = $myVote;
		}
		return $result;
	}
	
	/**
	 * @todo
	 * @return ArticleFormFactory New/edit clanku
	 */
	public function createComponentManageVote() {
		$types = $this->getVoteTypes(TRUE);
		$vote = isset($this->myVote) ? $this->myVote : NULL;
		$form = $this->voteForm->create($types, $vote);
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Votes:default');
		};
		
		return $form;
	}
	
	/**
	 * Vraci typy anketnich otazek
	 * @param boolean $simple Vracet zjednodusene pole id => nazev pro formular?
	 * @return Model\Entities\TypeVote[]
	 */
	private function getVoteTypes($simple=FALSE) {
		if (!isset($this->voteTypes)) {
			try {
				$this->voteTypes = $this->voteRepository->findAllVoteTypes();
				if (!$this->voteTypes) {
					$this->voteTypes = [];
				}
			} catch (\PDOException $e) {
				\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
				$this->voteTypes = [];
			}
		}
		if (!$simple) {
			return $this->voteTypes;
		}
		$result = [];
		foreach ($this->voteTypes as $type) {
			$result[$type->getId()] = $type->getName();
		}
		return $result;
	}
	
	/********** action & render NEW **********/
	
	public function actionNew() {
		
	}
	
	public function renderNew() {
		
	}
	
	/********** action & render EDIT **********/
	
	/**
	 * @param int $voteId vote_id, pkey ankety
	 */
	public function actionEdit($voteId) {
		$this->myVote = $this->voteRepository->getById($voteId);
		if (!$this->myVote) {
			$this->error($this->translator->translate('system.invalidId'));
			//$this->flashMessage($this->translator->translate('system.invalidId'), self::MESSAGE_DANGER);
		}
	}
	
	public function renderEdit() {
		
	}
	

}