<?php

namespace core;

class Route
{

	static function start()
	{
		// контроллер и действие по умолчанию
		$controller_name = 'users';
		$action_name = 'index';
		
		$routes = explode('/', $_SERVER['REQUEST_URI']);

		// получаем имя контроллера
		if ( !empty($routes[1]) )
		{	
			$controller_name = $routes[1];
		}
		
		// получаем имя экшена
		if ( !empty($routes[2]) )
		{
			$action_name = $routes[2];
		}

        // получаем остальные параметры
        $request = array();
        if (count($routes) > 3) {
            for($i = 3; $i < count($routes); $i++) {
                $request[] = $routes[$i];
            }
        }

		// добавляем префиксы
		$model_name = 'Model_'.$controller_name;
		$controller_name = 'Controller_'.$controller_name;
		$action_name = 'action_'.$action_name;

		// подцепляем файл с классом модели (файла модели может и не быть)
		$model_file = strtolower($model_name).'.php';
		$model_path = "application/models/".$model_file;
		if(file_exists($model_path))
		{
			include "application/models/".$model_file;
		}

		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name).'.php';
		$controller_path = "application/controllers/".$controller_file;
		if(file_exists($controller_path)) {
			include "application/controllers/".$controller_file;
		}
		else {
			Route::ErrorPage404();
		}
		
		// создаем контроллер
		$controller = new $controller_name;
		$action = $action_name;

        // Проверяем на наличие фильтров в контроллере
        $filters = $controller->behaviors();
        if (!empty($filters)) {
            foreach($filters as $filter) {
                if (class_exists($filter['class'])) {
                    $behavior = new $filter['class'];

                    $behavior->setParams($filter['params'], $action_name);

                    $action = $behavior->filter();
                }
            }
        }

		if(method_exists($controller, $action))
		{
			// вызываем действие контроллера
            $r = new \ReflectionMethod($controller, $action);


            $params = $r->getNumberOfRequiredParameters();

            if ($params > 0) {
                $diff = $params - count($request);
                if ($diff > 0) {
                    for($i = 0; $i < $diff; $i++) {
                        $request[] = false;
                    }
                }
            }

            call_user_func_array(array($controller, $action), $request);
		}
		else
		{
			// здесь также разумнее было бы кинуть исключение
			Route::ErrorPage404();
		}
	
	}

	function ErrorPage404()
	{
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
        header('HTTP/1.1 404 Not Found');
		header("Status: 404 Not Found");
		header('Location:'.$host.'404');
    }
    
}
