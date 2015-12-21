<?php

namespace App;

use Nette;

/**
 * Zakladni nastaveni pro cely blog, BlogSettings
 * @author Jiri Krupnik
 */
class BlogSettings extends Nette\Object {
	
	/** @var string $webSiteName Nazev celeho webu blogu */
	public $webSiteName;
	/** @var string $webSiteUrl Odkaz URL na blog */
	public $webSiteUrl;
	/** @var string $webSiteEmail Kontakt na web blogu */
	public $webSiteEmail;


	/**
	 * @param array $settings
	 */
	public function __construct($settings) {
		$this->webSiteEmail = $settings['webSiteEmail'];
		$this->webSiteName = $settings['webSiteName'];
		$this->webSiteUrl = $settings['webSiteUrl'];
	}
}
