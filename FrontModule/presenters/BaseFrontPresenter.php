<?php
namespace App\FrontModule\Presenters;

use App\Presenters;
use App\Model;


/**
 * Base presenter pro modul Front
 */
abstract class BaseFrontPresenter extends Presenters\BasePresenter {
	const 
		POST_PER_PAGE = 5;
	
	/** @var Model\Repository\SectionRepository @inject */
	public $sectionRepository;
	
	 /** @var \Texy\Texy @inject */
	public $texy;


	public function beforeRender() {
		//nastaveni spravne cesty k obrazkum pro Texy
		//$this->texy->imageModule->root = $this->template->basePath . '/images/'; 
		$this->texy->imageModule->root = '/images/'; 
		
		$this->template->today = new \Nette\Utils\DateTime();
		$this->template->sections = $this->sectionRepository->getAllSections();
	}
	
}