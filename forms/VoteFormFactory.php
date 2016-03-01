<?php
namespace App\Forms;

use Nette;
use Nette\Utils\DateTime;
use Nette\Application\UI\Form;
use Nette\Forms\Container;

/**
 * Form pro zalozeni nove anketni otazky
 */
class VoteFormFactory extends Nette\Object {
	/** @var \App\Model\Repository\VoteRepository */
	private $repository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;
	/** @var string Format (maska) datumu */
	private static $dateMask = 'd. m. Y, H:i';
	
	/**
	 * @param \App\Model\Repository\VoteRepository $repository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(\App\Model\Repository\VoteRepository $repository, BaseFormFactory $baseFormFactory) {	
		$this->baseFormFactory = $baseFormFactory;
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
	}
	
	/**
	 * @param array $types
	 * @param \App\Model\Entities\Vote $vote
	 * @return Form 
	 */
	public function create($types, $vote=NULL) {
		$form = $this->baseFormFactory->create();
		$form->addText('question', 'system.voteQuestion')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$form->addSelect('type', 'system.voteType')
			->setItems($types)
			->setPrompt('Zvolte typ dotazu')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));
		
		$form->addText('expiration', 'system.voteExpiration')
				->setType('datetime-local')
				->setAttribute('placeholder', 'd. m. Y, H:i (den. měsíc. rok, hodina:minuta');

		$options = $form->addDynamic('options', function(Container $option) {
			$option->addText('option', 'system.voteOption');
			$option->addSubmit('remove', 'system.delete')
					->addRemoveOnClick();
		}, 1);
		
		$options->addSubmit('add', 'system.new')
				->setValidationScope(FALSE)
				->addCreateOnClick();
		
		$form->addSubmit('send', 'system.save');
		
		$form->addHidden('id');
		
		if ($vote) {
			$defaults = $this->getDefaults($vote);
			//\Tracy\Debugger::log($defaults['options']);
			$form->setDefaults($defaults);
				$options->setValues($defaults['options']);
		}
		
		
		$form->onValidate[] = [$this, 'validateForm'];
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * Validace formulare se clankem: datum zverejneni
	 * @param Form $form
	 */
	public function validateForm(Form $form) {
		$values = $form->getValues();
		if (!empty($values->expiration)) {
			$expiration = DateTime::createFromFormat(self::$dateMask, $values->expiration);
			$today = new DateTime();
			if (!$expiration) {
				//neni validni datum
				$item = $form->getTranslator()->translate('system.voteExpiration');
				$form->addError($form->getTranslator()->translate('system.formFormat', ['item' => $item, 'format' => self::$dateMask]));
			} elseif ($expiration < $today) {
				$item = $form->getTranslator()->translate('system.voteExpiration');
				$form->addError($form->getTranslator()->translate('system.formFormat', ['item' => $item, 'format' => self::$dateMask]));
			}
		}
	}
	
	/**
	 * Zpracovani formulare s anketou
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		if (!empty($values->expiration)) {
			$expiration = DateTime::createFromFormat(self::$dateMask, $values->expiration);
			$values->expiration = $expiration;
		} else {
			$values->expiration = NULL;
		}
		if (empty($values->id)) {
			//novy
			$result = $this->newVote($values);
		} else {
			//editace
			$result = $this->editVote($values);
		}
		if ($result) {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestS'), 'success');
		} else {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestN'), 'danger');
		}
		
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values
	 * @return boolean Vytvoreni nove ankety problehlo uspesne?
	 */
	protected function newVote($values) {
		$result = TRUE;
		try {
			//pridani nove ankety a ulozeni zmen
			$newVote = new \App\Model\Entities\Vote($values->question);
			$newVote->setExpire($values->expiration);
			
			if (!empty($values->type)) {
				$voteType = $this->em->getReference(\App\Model\Entities\TypeVote::class, $values->type);
				$newVote->setTypeVote($voteType);
			}
			foreach ($values->options as $option) {
				$newOption = new \App\Model\Entities\Option($option->option);
				$this->em->persist($newOption);
				$newVote->addOption($newOption);
			}
			$this->em->persist($newVote);
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values Hodnoty z formulare
	 * @return boolean Editace provedena uspesne?
	 */
	protected function editVote($values) {
		$result = TRUE;
		try {
			/** @var \App\Model\Entities\Vote $editVote */
			$editVote = $this->repository->getById($values->id);
			if (!$editVote) {
				return FALSE;
			}
			// nastaveni atributu
			$editVote->setQuestion($values->question);
			
			$editVote->setExpire($values->expiration);
			
			if ($editVote->getTypeVote()->getId() !== $values->type) {
				$typeVote = $this->em->getReference(\App\Model\Entities\TypeVote::class, $values->id);
				$editVote->setTypeVote($typeVote);
			}
			
			$options = [];
			foreach ($values->options as $option) {
				if (empty($option->option)) continue;
				$options[] = $option->option;
			}
			$result = $editVote->setOptions($options);
			foreach ($result['remove'] as $removeOption) {
				if ($removeOption === NULL)	continue;
				$this->em->remove($removeOption);
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
	 * @param \App\Model\Entities\Vote $vote
	 * @return array Vychozi hodnoty pro formular
	 */
	protected function getDefaults($vote) {
		$result = [];
		
		$result['id'] = $vote->getId();
		$result['question'] = $vote->getQuestion();
		$result['type'] = $vote->getTypeVote()->getId();
		
		$expiration = $vote->getExpire();
		if ($expiration) {
			$result['expiration'] = $vote->getExpire()->format(self::$dateMask);
		}
		
	
		$options = $vote->getOptions();	
		$result['options'] = [];
		$i = 1; //containery v replicatoru se cislu od jednicky
		foreach ($options as $option) {
			$result['options'][$i] = ['option' => $option->getValue()];
			$i++;
		}
		
		//$result['options'] 
		return $result;
	}

}