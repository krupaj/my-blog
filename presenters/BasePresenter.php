<?php
namespace App\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
	const 
		MESSAGE_INFO = 'info',
		MESSAGE_DANGER = 'danger',
		MESSAGE_SUCCESS = 'success';
	
	/** @var \Kdyby\Translation\Translator */
	public $translator;
	
	public function injectTranslator(\Kdyby\Translation\Translator $translator) {
		$this->translator = $translator;
	}
	
}
