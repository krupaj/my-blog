<?php
namespace App\FrontModule\Presenters;

use App\Presenters;
use App\Model;


/**
 * Base presenter pro modul Front
 */
abstract class BaseFrontPresenter extends Presenters\BasePresenter {
	const 
		POST_PER_PAGE = 2;
	
	/** @var Model\Repository\SectionRepository @inject */
	public $sectionRepository;


	public function beforeRender() {
		$this->template->today = new \Nette\Utils\DateTime();
		$this->template->sections = $this->sectionRepository->getAllSections();
	}
	
}