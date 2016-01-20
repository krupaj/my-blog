<?php
namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class SectionFormFactory extends Nette\Object {
	/** @var \App\Model\Repository\SectionRepository */
	private $repository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;

	/**
	 * @param \App\Model\Repository\SectionRepository $repository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(\App\Model\Repository\SectionRepository $repository, \App\Forms\BaseFormFactory $baseFormFactory) {
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
		$this->baseFormFactory = $baseFormFactory;
	}

	/**
	 * @param \App\Model\Entities\Section|NULL $section Clanek k editaci
	 * @return Form 
	 */
	public function create($section = NULL) {
		$form = $this->baseFormFactory->create();
		$form->addText('title', 'system.title')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$form->addTextArea('description', 'system.description')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']))
			->setAttribute('rows', 10);

		$form->addSubmit('send', 'system.save');
		
		//id, vychozi hodnoty pri editaci
		$form->addHidden('id');
		if ($section !== NULL) {
			$defaults = $this->getDefaults($section);
			$form->setDefaults($defaults);
		}

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * Zpracovani formulare s rubrikou
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		//nova rubrika nebo jeji editace
		$result = empty($values->id) ? $this->newSection($values) : $this->editSection($values);
		
		if ($result) {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestS'));
		} else {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestN'));
		}
		
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values
	 * @return boolean Vytvoreni nove rubriky problehlo uspesne?
	 */
	protected function newSection($values) {
		$result = TRUE;
		try {
			$newSection = new \App\Model\Entities\Section($values->title, $values->description);
			//pridani nove rubriky a ulozeni zmen
			$this->em->persist($newSection);
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
	protected function editSection($values) {
		$result = TRUE;
		try {
			/** @var \App\Model\Entities\Section */
			$editSection = $this->repository->getById($values->id);
			if (!$editSection) {
				return FALSE;
			}
			// nastaveni atributu a ulozeni zmeny
			$editSection->setTitle($values->title);
			$editSection->setDescription($values->description);
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param \App\Model\Entities\Section $section
	 * @return array Vychozi hodnoty pro formular
	 */
	protected function getDefaults($section) {
		$result = [];
		$result['id'] = $section->getId();
		$result['title'] = $section->getTitle();
		$result['description'] = $section->getDescription();
		
		return $result;
	}

}