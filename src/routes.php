<?php

    /*
    |--------------------------------------------------------------------------
    | Rota de Exemplo
    |--------------------------------------------------------------------------
    |
    | Esta é uma rota de exemplo apenas para teste.
    | Está disponivel apenas quando o Laravel é executado em modo de Debug.
    | Com as diretivas do arquivo .env setadas adequadamente:
    |
    | APP_DEBUG=true
    | APP_ENV=local
    */

    Route::namespace('ReportCollection\Http\Controllers')->middleware(['web'])->group(function () {

        Route::get('/report-collection', 'ExampleController@index')->name('report-collection.index');

    });
