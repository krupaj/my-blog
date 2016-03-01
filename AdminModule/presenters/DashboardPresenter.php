<?php
namespace App\AdminModule\Presenters;

use App\Model;


final class DashboardPresenter extends BaseAdminPresenter {
	
	public function actionDefault() {
		$this->template->title = $this->translator->translate('system.adminSection');
	}
	

}
