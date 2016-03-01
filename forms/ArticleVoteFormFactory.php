<?php
namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\Repository\VoteRepository;
use App\Model\Repository\ArticleRepository;

/**
 * Form na propojeni ankety a clanku
 */
class ArticleVoteFormFactory extends Nette\Object {
	/** @var VoteRepository */
	private $voteRepository;
	/** @var ArticleRepository */
	private $articleRepository;
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	/** @var BaseFormFactory */
	private $baseFormFactory;
	
	/**
	 * @param VoteRepository $voteRepository
	 * @param ArticleRepository $articleRepository
	 * @param BaseFormFactory $baseFormFactory Tovarna se zakladni formularem
	 */
	public function __construct(VoteRepository $voteRepository, ArticleRepository $articleRepository, BaseFormFactory $baseFormFactory) {	
		$this->baseFormFactory = $baseFormFactory;
		$this->voteRepository = $voteRepository;
		$this->articleRepository = $articleRepository;
		$this->em = $voteRepository->getEntityManager();
	}
	
	/**
	 * @return Form 
	 */
	public function create($mode) {
		$form = $this->baseFormFactory->create();
		$items = $this->getListItem();
		$form->addSelect('item', 'items', $items);
		$form->addHidden('mode', $mode);
		$form->addSubmit('send', 'system.save');
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}
	
	/**
	 * Zpracovani formulare s anketou
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values) {
		$result = TRUE;
		if ($result) {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestS'), 'success');
		} else {
			$form->getPresenter()->flashMessage($form->getTranslator()->translate('system.requestN'), 'danger');
		}
	}
	
	protected function getListItem($mode) {
		$result = [];
		if ($mode == 'article') {
			//$this->articleRepository->fin
		} elseif ($mode == 'votes') {
			
		}
		return $result;
	}
	
	

}