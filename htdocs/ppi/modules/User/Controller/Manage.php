<?php
namespace User\Controller;

use PPI\Module\Controller as BaseController;

class Manage extends BaseController {
	
	protected $userStorage;

	public function helloAction() {
		return $this->render('User:manage:hello.html.php');
	}
	
	public function indexAction() {
		
		return $this->render('User:manage:index.html.php');
	}
	
	public function indextwigAction() {
		return $this->render('User:manage:index.html.twig');
	}
	
	public function createAction() {
		
		if($this->is('post')) {
			$us = $this->getUserStorage();
			$post = $this->post();
			$us->insert(array(
				
			));
		}
		
		$this->redirect($this->generateUrl('Homepage'));
	}
	
	protected function getUserStorage() {
		return new \User\Storage\User($this->getService('DataSource'));
	}
	
}
