<?php
class Controller{
	protected $isAdmin;
	function __construct($_isadmin = false){
		$this->isAdmin = $_isadmin;
		session_start();
		if(preg_match('/gzip/',$_SERVER['HTTP_ACCEPT_ENCODING'])){
			ob_start('ob_gzhandler');
		}else{
			ob_start();
		}
		header("X-Powered-By: FeelyFramework/2.0", true);
		if(Core::config('is_acao')){
			header("Access-Control-Allow-Origin: *", true);
        }
		if(Core::config('is_hsts')){
			header("Strict-Transport-Security: max-age=2592000; includeSubdomains; preload", true);
		}
	}
	protected function display($assign, $viewName){
		if($this->isAdmin){
			$themeName = Core::config('AdminTheme');
		}else{
			$themeName = Core::config('Theme');
		}
		$view = new View($themeName, $viewName);
		$view->set($assign);
		$view->render();
		ob_flush();
	}

	protected function returnState($httpCode = 204){
        if(Core::config('is_acao')){
            header("Access-Control-Allow-Origin: *", true);
            header("Access-Control-Allow-Method: POST", true);
            header("Access-Control-Allow-Headers: Content-Type", true);
        }
        header("X-Powered-By: FeelyFramework/2.0", true, $httpCode);
        ob_flush();
    }

	protected function returnJson($json, $httpCode = 200){
        header("Content-Type: application/json; charset=UTF-8", true);
	    header("X-Powered-By: FeelyFramework/2.0", true, $httpCode);
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        ob_flush();
    }
}