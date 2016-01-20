<?php
namespace App\Forms;

use Nette;
use Nette\Utils\DateTime;
use Nette\Application\UI\Form;
use \Kdyby\Translation\Phrase;

class ArticleFormFactory extends Nette\Object {
	/** @var \App\Model\Repository\ArticleRepository */
	private $repository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;
	/** @var string Format (maska) datumu */
	private static $dateMask = 'd. m. Y, H:i';

	/**
	 * @param \App\Model\Repository\ArticleRepository $repository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(\App\Model\Repository\ArticleRepository $repository, \App\Forms\BaseFormFactory $baseFormFactory) {
		$this->repository = $repository;
		$this->em = $repository->getEntityManager();
		$this->baseFormFactory = $baseFormFactory;
	}


	/**
	 * @param \App\Model\Entities\Article|NULL $article Clanek k editaci
	 * @return Form 
	 */
	public function create($article = NULL, $sections = []) {
		$form = $this->baseFormFactory->create();
		$form->addText('title', 'system.postName')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));

		$form->addText('description', 'system.postDescription')
			->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']));
		
		$form->addCheckbox('published', 'system.isPublished');
		
		//chybi publish_date - datum zverejneni
		$today = new DateTime();
		$form->addText('publishDate', 'system.published')
				->setType('datetime-local')
				->setDefaultValue($today->format('d. m. Y, H:i'));
				//->setAttribute('placeholder', 'd. m. Y, H:i (den. měsíc. rok');
		
		$form->addSelect('section', new Phrase('system.section',1))
				->setItems($sections)
				->setPrompt('Zvolte sekci');

		$form->addTextArea('content', 'system.postContent')
				->setRequired($form->getTranslator()->translate('system.requiredItem', ['label' => '%label']))
				->setAttribute('rows', 10);

		$form->addSubmit('send', 'system.save');
		
		//id, vychozi hodnoty pri editaci
		$form->addHidden('id');
		if ($article !== NULL) {
			$defaults = $this->getDefaults($article);
			$form->setDefaults($defaults);
		}
		$form->onValidate[] = [$this, 'validateForm'];
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * @todo
	 * @param Form $form
	 */
	public function validateForm(Form $form) {
		$values = $form->getValues();
		$publishDate = DateTime::createFromFormat(self::$dateMask, $values->publishDate);
		if (!$publishDate) {
			//neni validni datum
			$item = $form->getTranslator()->translate('system.published');
			$form->addError($form->getTranslator()->translate('system.formFormat', ['item' => $item, 'format' => self::$dateMask]));
		}
	}
	
	/**
	 * Zpracovani formulare s clankem
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		if (empty($values->publishDate)) {
			$date = new DateTime();
		} else {
			$date = DateTime::createFromFormat('d. m. Y, H:i', $values->publishDate);
		}
		$values->offsetSet('publishDate', $date);
		//novy clanek nebo jeho editace
		$result = empty($values->id) ? $this->newArticle($values) : $this->editArticle($values);
		
		if ($result) {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestS'));
		} else {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestN'));
		}
		
	}
	
	/**
	 * @param Nette\Utils\ArrayHash $values
	 * @return boolean Vytvoreni noveho clanku problehlo uspesne?
	 */
	protected function newArticle($values) {
		$result = TRUE;
		try {
			$newArticle = new \App\Model\Entities\Article($values->title, $values->description, $values->content, $values->publishDate, $values->published);
			//pridani noveho clanku
			$this->em->persist($newArticle);
			//ulozeni zmen
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
	protected function editArticle($values) {
		$result = TRUE;
		try {
			/** @var \App\Model\Entities\Article $editArticle */
			$editArticle = $this->repository->getById($values->id);
			if (!$editArticle) {
				return FALSE;
			}
			// nastaveni atributu
			$editArticle->setPublished($values->published);
			$editArticle->setTitle($values->title);
			$editArticle->setUpdateDate();
			$editArticle->setPublishDate($values->publishDate);
			//ulozeni zmeny
			$this->em->flush();
		} catch (\Exception $e) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::INFO);
			$result = FALSE;
		}
		return $result;
	}
	
	/**
	 * @param \App\Model\Entities\Article $article
	 * @return array Vychozi hodnoty pro formular
	 */
	protected function getDefaults($article) {
		$result = [];
		$result['id'] = $article->getId();
		$result['title'] = $article->getTitle();
		$result['description'] = $article->getDescription();
		$result['published'] = $article->isPublished();
		$publishDate = $article->getPublishDate();
		if ($publishDate) {
			$result['publishDate'] = $publishDate->format('d. m. Y, H:i');
		}
		$result['content'] = $article->getContent();
		$result['section'] = $article->getSection()->getId();
		return $result;
	}

}