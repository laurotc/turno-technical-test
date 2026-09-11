<?php

use App\Contracts\ShippingLabelPurchaser;
use App\Services\EasyPost\EasyPostShippingLabelPurchaser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withSingletons([
        ShippingLabelPurchaser::class => EasyPostShippingLabelPurchaser::class,
    ])
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
