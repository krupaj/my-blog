<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BaseFrontPresenter extends Nette\Application\UI\Presenter {
	const 
		POST_PER_PAGE = 2;
	
	/** @var \Kdyby\Translation\Translator @inject */
	public $translator;
	
	/** @var Model\Repository\SectionRepository @inject */
	public $sectionRepository;


	public function beforeRender() {
		$this->template->today = new \Nette\Utils\DateTime();
		$this->template->sections = $this->sectionRepository->getAllSections();
	}
	
	

}
