<?php
namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class BaseFormFactory extends Nette\Object {
	/** @var Nette\Localization\ITranslator */
	private $translator;

	public function __construct(Nette\Localization\ITranslator $translator) {
		$this->translator = $translator;
	}

	/**
	 * @return Form
	 */
	public function create() {
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new \Nextras\Forms\Rendering\Bs3FormRenderer);

		return $form;
	}
}