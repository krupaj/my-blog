<?php
namespace App\Model;

use Nette;

/**
 * Zakladni operace s obrazky clanku
 * @author Jiri Krupnik
 */
class ArticleImageStorage extends Nette\Object {
	/** @var string */
	private $wwwDir;
	
	/** @var string */
	private $wwwDirUser;

	/**
	 * @param string $wwwDir
	 */
	public function __construct($wwwDir) {
		$this->wwwDir = $wwwDir . '/images/articles/';
		$this->wwwDirUser = $wwwDir . '/images/users/';
	}
	
	/**
	 * @param Entities\Article $article
	 * @param Nette\Utils\Image $image
	 * @param string $source Zdroj obrazku (napr. Flick, atd.)
	 * @return boolean
	 */
	public function setArticleImage($article, $image, $source = NULL) {
		//odstranit puvodni obr vcetne nahledu
		$this->deleteArticleImage($article);
		//vytvorit nahled a ulozit novy obr
		$imageName = $article->getId() . '_' . $article->getWebalizeTitle(20) . '.jpg';
		$thumbnail = 'pre' . '_' . $imageName;
		$resImg = $image->save($this->wwwDir . $imageName);
		if (!$resImg) {
			return FALSE;
		}
		if (empty($source)) {
			$source = NULL;
		}
		$myImage = new Entities\Image($imageName, $source);
		$myImage->thumbnail = $thumbnail;
		$article->setImage($myImage);
		$resImg2 = $image->save($this->wwwDir . $thumbnail, 50);
		
		return ($resImg && $resImg2);
	}
	
	/**
	 * Odstranuje obrazek clanku vcetne nahledu
	 * @param Entities\Article $article
	 */
	public function deleteArticleImage($article) {
		if (!$article->hasImage()) return;
		
		//odstranit puvodni obr vcetne nahledu
		$oldImage = $article->getImage();
		$oldImagePath = $this->wwwDir . $oldImage->image;
		if (file_exists($oldImagePath)) {
			unlink($oldImagePath);
		}
		$oldImagePath = $this->wwwDir . $oldImage->getThumbnail();
		if (file_exists($oldImagePath)) {
			unlink($oldImagePath);
		}
		$article->setImage();
	}
	
	/**
	 * @param Entities\Person $person
	 * @param Nette\Utils\Image $image
	 * @param string $source Zdroj obrazku (napr. Flick, atd.)
	 * @return boolean
	 */
	public function setPersonAvatar($person, $image, $source = NULL) {
		//odstranit puvodni obr vcetne nahledu
		$this->deletePersonAvatar($person);
		//vytvorit nahled a ulozit novy obr
		$imageName = $person->id . '_' . $person->getWebalizeName() . '.jpg';
		$thumbnail = 'avatar' . '_' . $imageName;
		$resImg = $image->save($this->wwwDirUser . $imageName);
		if (!$resImg) {
			return FALSE;
		}
		if (empty($source)) {
			$source = NULL;
		}
		$myImage = new Entities\Image($imageName, $source);
		$myImage->thumbnail = $thumbnail;
		$person->setAvatar($myImage);
		$image->resize(100, NULL, \Nette\Utils\Image::SHRINK_ONLY);
		$resImg2 = $image->save($this->wwwDirUser . $thumbnail, 50);
		
		return ($resImg && $resImg2);
	}
	
	/**
	 * Odstranuje obrazek clanku vcetne nahledu
	 * @param Entities\Person $person
	 */
	public function deletePersonAvatar($person) {
		if (!$person->hasAvatar()) return;
		
		//odstranit puvodni obr vcetne nahledu
		$oldImage = $person->avatar;
		$oldImagePath = $this->wwwDirUser . $oldImage->image;
		if (file_exists($oldImagePath)) {
			unlink($oldImagePath);
		}
		$oldImagePath = $this->wwwDirUser . $oldImage->getThumbnail();
		if (file_exists($oldImagePath)) {
			unlink($oldImagePath);
		}
		$person->setAvatar();
	}
	
}