<?php
namespace App\AdminModule\Presenters;

use App\Model;
use App\Forms\TagFormFactory;

/**
 * Sprava rubrik (sekci)
 * @author Jiri Krupnik <krupaj@seznam.cz>
 * @package \App\AdminModule\Presenters
 */
final class TagsPresenter extends BaseAdminPresenter {
	/** @var TagFormFactory @inject */
	public $tagForm;
	/** @var \App\Model\Repository\TagRepository @inject */
	public $tagRepository;
	/** @var Model\Entities\Tag|NULL */
	private $myTag = NULL;
	/** @var Model\Entities\Tag[] */
	private $myTags;
	
	public function renderDefault() {
		$this->template->tags = $this->getTags();
		$this->template->title = $this->translator->translate('system.tag', 2);
	}
	
	/**
	 * @return Model\Entities\Tag[]
	 */
	private function getTags() {
		if (!isset($this->myTags)) {
			$this->myTags = $this->tagRepository->getAllTags();
		}
		return $this->myTags;
	}
	
	/**
	 * Pozadavek pro pridani noveho clanku
	 */
	public function handleNewTag() {
		$this->template->title = $this->translator->translate('system.new');
		$this->template->component = 'manageTag';
		$this->redrawControl('formContainer');
	}
	
	/**
	 * @param int $tagId
	 * @return void Editace tagu (nalepky, stitku)
	 */
	public function handleEditTag($tagId) {
		$this->myTag = $this->tagRepository->getById($tagId);
		if (!$this->myTag) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		
		$this->template->title = $this->translator->translate('system.edit');
		$this->template->component = 'manageTag';
		$this->redrawControl('formContainer');
	}
	
	/**
	 * @param int $tagId
	 * @return void Odstraneni sekce
	 */
	public function handleDeleteTag($tagId) {
		$this->myTag = $this->tagRepository->getById($tagId);
		if (!$this->myTag) {
			$this->flashMessage($this->translator->translate('system.invalidId'));
			return;
		}
		$result = $this->tagRepository->deleteTag($this->myTag);
		if ($result) {
			$this->flashMessage($this->translator->translate('system.requestS'), self::MESSAGE_SUCCESS);
		} else {
			$this->flashMessage($this->translator->translate('system.requestN'), self::MESSAGE_DANGER);
		}
		$this->redirect('this');
	}
	
	/**
	 * @return TagFormFactory New/edit clanku
	 */
	public function createComponentManageTag() {
		$form = $this->tagForm->create($this->myTag);
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('this');
		};
		return $form;
	}
	
	
	
	

}