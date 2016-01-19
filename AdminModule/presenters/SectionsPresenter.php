<?php
namespace App\AdminModule\Presenters;

use App\Model;
use App\Forms\SectionFormFactory;

/**
 * Sprava rubrik (sekci)
 * @author Jiri Krupnik <krupaj@seznam.cz>
 * @package \App\AdminModule\Presenters
 */
final class SectionsPresenter extends BaseAdminPresenter {
	/** @var SectionFormFactory @inject */
	public $sectionForm;
	/** @var \App\Model\Repository\SectionRepository @inject */
	public $sectionRepository;
	/** @var Model\Entities\Section|NULL */
	private $mySection = NULL;
	/** @var Model\Entities\Section[] */
	private $mySections;
	
	public function renderDefault() {
		$this->template->sections = $this->getSections();
	}
	
	/**
	 * @return Model\Entities\Section[]
	 */
	private function getSections() {
		if (!isset($this->mySection)) {
			$this->mySections = [];
			$this->mySections = $this->sectionRepository->getAllSections();
		}
		return $this->mySections;
	}
	
	/**
	 * Pozadavek pro pridani noveho clanku
	 */
	public function handleNewSection() {
		$this->template->title = $this->translator->translate('system.newSection');
		$this->template->component = 'manageSection';
		$this->redrawControl('formContainer');
	}
	
	/**
	 * @param int $sectionId
	 * @return void Editace rubriky
	 */
	public function handleEditSection($sectionId) {
		$this->mySection = $this->sectionRepository->getById($sectionId);
		if (!$this->mySection) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		
		$this->template->title = $this->translator->translate('system.editPost');
		$this->template->component = 'manageSection';
		$this->redrawControl('formContainer');
	}
	
	/**
	 * @param int $sectionId
	 * @return void Odstraneni sekce
	 */
	public function handleDeleteSection($sectionId) {
		$this->mySection = $this->sectionRepository->getById($sectionId);
		if (!$this->mySection) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		$result = $this->sectionRepository->deleteSection($this->mySection);
		if ($result) {
			$this->flashMessage($this->translator->translate('system.requestS'), self::MESSAGE_SUCCESS);
		} else {
			$this->flashMessage($this->translator->translate('system.requestN'), self::MESSAGE_DANGER);
		}
		$this->redirect('this');
	}
	
	/**
	 * @return SectionFormFactory New/edit clanku
	 */
	public function createComponentManageSection() {
		$form = $this->sectionForm->create($this->mySection);
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('this');
		};
		return $form;
	}
	
	
	
	

}