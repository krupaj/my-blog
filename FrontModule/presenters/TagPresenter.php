<?php
namespace App\FrontModule\Presenters;

use App\Model;


final class TagPresenter extends BaseFrontPresenter {
	
	/** @var Model\Repository\TagRepository @inject */
	public $tagRepository;
	/** @var \App\Model\Entities\Tag[] */
	private $tags;
	
	public function actionDefault() {	
		$this->template->bgImage = "home-bg.jpg";
	}
	
	public function renderDefault() {
		$this->template->tagGroups = $this->getTagGroups($this->getTags());
		
	}
	
	/**
	 * @param Model\Entities\Tag[] $tags
	 * @return array Serezene skupiny Tagu
	 */
	private function getTagGroups($tags) {
		$result = [];
		$others = [];
		foreach ($tags as $tag) {
			$firstLetter = $tag->getFirstLetter();
			if (!ctype_alpha($firstLetter)) {
				//neni-li to pismenko z abecedy, potom patri do kategorie Ostatni
				$others[] = $tag;
			} else {
				$result[$firstLetter][] = $tag;
			}
		}
		//ostatni nakonec
		if (!empty($others)) {
			$firstLetter = $this->translator->translate('system.others');
			$result[$firstLetter] = $others;
		}
		return $result;
	}
	
	/**
	 * Vraci vsechny pouzite tagy
	 * @return Model\Entities\Tag[]
	 */
	public function getTags() {
		if (!isset($this->tags)) {
			$this->tags = $this->tagRepository->getAllTags();
		}
		return $this->tags;
	}
	
}
