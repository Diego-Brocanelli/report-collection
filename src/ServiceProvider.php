<?php

namespace ReportCollection;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Em modo de desenvolvimento, as rotas de exemplo são carregadas
        if (env('APP_DEBUG') || env('APP_ENV') === 'local') {
        	
        	$this->loadViewsFrom(__DIR__.'/resources/views', 'report-collection');
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }

        \ReportCollection::loadHelpers();
    }
}
