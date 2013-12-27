<?php
namespace SlimBluescreen;

class BlueScreenMiddleware extends \Slim\Middleware
{
	public function call()
	{
		try {
			$this->next->call();
		} catch (\Exception $e) {
			$bluescreen = new BlueScreen();

			// render view
			$this->app->contentType('text/html');
			$this->app->response()->status(500);
			$this->app->response()->body($bluescreen->render($e));
		}
	}
} 