<?php
namespace App\Modules\Experts\Providers;

use App;
use Config;
use Lang;
use View;
use Illuminate\Support\ServiceProvider;

class ExpertsServiceProvider extends ServiceProvider
{
	
	public function register()
	{
		
		
		
		App::register('App\Modules\Experts\Providers\RouteServiceProvider');

		$this->registerNamespaces();
	}

	
	protected function registerNamespaces()
	{
		Lang::addNamespace('experts', realpath(__DIR__.'/../Resources/Lang'));

		View::addNamespace('experts', base_path('resources/views/vendor/experts'));
		View::addNamespace('experts', realpath(__DIR__.'/../Resources/Views'));
	}
}
