<?php

use Illuminate\Support\Facades\Route;
use ClarionApp\RssTorrents\Controllers\FeedsController;
use ClarionApp\RssTorrents\Controllers\SeriesController;

Route::group(['middleware'=>['auth:api'], 'prefix'=>$this->routePrefix ], function () {
    Route::get('/feeds/urls', [FeedsController::class, 'getUrls']);
    Route::get('/feeds/torrents', [FeedsController::class, 'getTorrents']);
    
    // Series resource routes
    Route::apiResource('series', SeriesController::class);
    
    // Additional series routes
    Route::get('/series-subscribed', [SeriesController::class, 'subscribed']);
    Route::patch('/series/{series}/toggle-subscription', [SeriesController::class, 'toggleSubscription']);
    Route::patch('/series/bulk-subscription', [SeriesController::class, 'bulkUpdateSubscription']);
});
