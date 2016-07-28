<?php

namespace AMPortal\DataEngine\Controllers;

//use AMPortal\DataEngine\Services\Services as Services;

class IndexController extends BaseController
{

    public function indexAction() {
        try {
        	//$this->view->workflows = Services::getService('Workflows')->getAll();
            echo "lol";
        } catch (\Exception $e) {
        	$this->flash->error($e->getMessage());
        }
    }	
}

