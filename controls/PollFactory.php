<?php
namespace App\Controls;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI;
use App\Model\Entities\Vote;
use App\Model\Repository\VoteRepository;

class PollFactory extends UI\Control {
	/** @var \App\Model\Repository\VoteRepository */
	private $repository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;
	/** @var Vote Anketni otazka dle ktere se vykresli formular na hlasovani */
	private $vote;
	/** @var Nette\Http\Request */
	private $request;


	/**
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(VoteRepository $repository, \App\Forms\BaseFormFactory $baseFormFactory, Nette\Http\Request $request) {	
		$this->baseFormFactory = $baseFormFactory;
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
		$this->request = $request;
	}
	
	/**
	 * Nastaveni anketni otazky
	 * @param Vote $vote
	 */
	public function setVote($vote) {
		$this->vote = $this->repository->getById($vote);
	}
	
	public function render() {
		$this->template->setFile(__DIR__ . '/templates/poll.latte');
		$this->template->vote = $this->vote;
		$this->template->pollForm = $this->isVotingAllowed() ? 'pollForm' : NULL;
		$this->template->render();
	}

	/**
	 * @return Form Hlasovaci formular
	 */
	public function createComponentPollForm() {
		$form = $this->baseFormFactory->create();
		$typeVote = $this->vote->getTypeVote();
		$typeVoteId = $typeVote->getId();
		if (in_array($typeVote->getFormType(), ['radio', 'checkbox'])) {
			$list = $this->getOptionList($this->vote->getOptions());
			if ($typeVote->getFormType() == 'radio') {
				$form->addRadioList($typeVoteId, 'options', $list);
			} elseif ($typeVote->getFormType() == 'checkbox') {
				$form->addCheckboxList($typeVoteId, 'options', $list);
			}
			if ($typeVote->hasOpenOption()) {
				$form->addText('text', 'text');
			}
		} elseif ($typeVote->getFormType() == 'text') {
			$form->addText($typeVoteId, 'options');
		} elseif ($typeVote->getFormType() == 'textarea') {
			$form->addTextArea($typeVoteId, 'options');
		}
		$form->addSubmit('send', 'system.save');
		$form->addHidden('id', $this->vote->getId());
		
		$form->onValidate[] = [$this, 'validateForm'];
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * @param \App\Model\Entities\Option[] $options
	 * @return array
	 */
	protected function getOptionList($options) {
		$result = [];
		foreach ($options as $option) {
			$result[$option->getId()] = $option->getValue();
		}
		return $result;
	}
	
	/**
	 * Validace formulare
	 * @param Form $form
	 */
	public function validateForm(Form $form) {
		$requestInfo = $this->getRequestInfo($this->request);
		if (is_null($requestInfo['ip']) || is_null($requestInfo['agent']) || is_null($requestInfo['cookie'])) {
			$form->addError('chyba');
		}
	}
	
	/**
	 * Zpracovani formulare s anketou
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		$requestInfo = $this->getRequestInfo($this->request);
		$result = $this->processPoll($this->vote, $requestInfo, $values);
		if ($result) {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestS'), 'success');
		} else {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestN'), 'danger');
		}
		
	}
	
	/**
	 * @param Vote $vote
	 * @param array $requestInfo
	 * @param \Nette\Utils\ArrayHash $values
	 * @return boolean Ulozeni hlasovani
	 */
	protected function processPoll($vote, $requestInfo, $values) {
		$voter_id = \App\Model\Entities\Poll::generateVoteIdentificator();
		foreach ($values as $id => $value) {
			if (!is_numeric($id) || ($value === FALSE)) continue;
			$myOption = $this->em->getReference(\App\Model\Entities\Option::class, $id);
			$myNewPoll = new \App\Model\Entities\Poll($myOption, $value);
			$myNewPoll->setVoterIdentification($voter_id);
			$myNewPoll->setIp($requestInfo['ip']);
			$myNewPoll->setAgent($requestInfo['agent']);
			$myNewPoll->setCookie($requestInfo['cookie']);
			$vote->addPoll($myNewPoll);
		}
		try {
			$this->em->flush();	
			$result = TRUE;
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param Nette\Http\Request $request
	 * @return array Http parametry hlasujiciho uzivatele
	 */
	protected function getRequestInfo($request) {
		$result = [
			'ip' => $request->getRemoteAddress(),
			'agent' => $request->getHeader('user-agent'),
			'cookie' => $request->getCookie('nette-browser')
		];
		return $result;
	}
	
	/**
	 * Je mozne hlasovat v ankete?
	 * @return boolean 
	 */
	protected function isVotingAllowed() {
		$requestInfo = $this->getRequestInfo($this->request);
		//existuje hlasovani s danymi parametry?
		$r = $this->vote->getPolls()->exists(function($key, $entity) use ($requestInfo) {
			return ($entity->getCookie() == $requestInfo['cookie']);
		});
		//existuje-li nelze znovu hlasovat
		return !$r;
	}

}

interface IPollFactory {
	/** @return PollFactory */
	public function create();
}