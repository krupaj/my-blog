<?php
namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
	/** @var \Kdyby\Translation\Translator */
	public $translator;
	
	public function injectTranslator(\Kdyby\Translation\Translator $translator) {
		$this->translator = $translator;
	}
	
}
