<?php
namespace App\FrontModule\Presenters;

use App\Presenters;

/**
 * Base presenter pro modul Front
 */
abstract class BaseFrontPresenter extends Presenters\BasePresenter {
	const 
		POST_PER_PAGE = 5;
	
	 /** @var \Texy\Texy @inject */
	public $texy;

	public function beforeRender() {
		//nastaveni spravne cesty k obrazkum pro Texy
		$this->texy->imageModule->root = $this->template->basePath . '/images/'; 
		$this->texy->setOutputMode(\Texy\Texy::HTML5); 
		$this->texy->figureModule->leftClass = 'pull-left';
		$this->texy->figureModule->rightClass = 'pull-right';
		$this->texy->imageModule->leftClass = 'pull-left';
		$this->texy->imageModule->rightClass = 'pull-right';
		
		$this->template->today = new \Nette\Utils\DateTime();
	}
	
}