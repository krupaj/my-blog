<?php
namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class TagFormFactory extends Nette\Object {
	/** @var \App\Model\Repository\TagRepository */
	private $repository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;

	/**
	 * @param \App\Model\Repository\TagRepository $repository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(\App\Model\Repository\TagRepository $repository, \App\Forms\BaseFormFactory $baseFormFactory) {
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
		$this->baseFormFactory = $baseFormFactory;
	}

	/**
	 * @param \App\Model\Entities\Tag|NULL $tag Tag (nalepka, stitek) k editaci
	 * @return Form 
	 */
	public function create($tag = NULL) {
		$form = $this->baseFormFactory->create();
		$form->addText('title', 'system.postName')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$form->addSubmit('send', 'system.save');
		
		//id, vychozi hodnoty pri editaci
		$form->addHidden('id');
		if ($tag !== NULL) {
			$defaults = $this->getDefaults($tag);
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
		$result = empty($values->id) ? $this->newTag($values) : $this->editTag($values);
		
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
	protected function newTag($values) {
		$result = TRUE;
		try {
			$newTag = new \App\Model\Entities\Tag($values->title);
			//pridani nove rubriky a ulozeni zmen
			$this->em->persist($newTag);
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
	protected function editTag($values) {
		$result = TRUE;
		try {
			/** @var \App\Model\Entities\Tag */
			$editTag = $this->repository->getById($values->id);
			if (!$editTag) {
				return FALSE;
			}
			// nastaveni atributu a ulozeni zmeny
			$editTag->setTitle($values->title);
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param \App\Model\Entities\Tag $tag
	 * @return array Vychozi hodnoty pro formular
	 */
	protected function getDefaults($tag) {
		$result = [];
		$result['id'] = $tag->getId();
		$result['title'] = $tag->getTitle();
		return $result;
	}

}