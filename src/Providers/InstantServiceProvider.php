<?php

namespace MohammadZarifiyan\LaravelLocaleKit\Providers;

use Illuminate\Support\ServiceProvider;
use MohammadZarifiyan\LaravelLocaleKit\Commands\Export;

class InstantServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		$this->publishes(
			[__DIR__.'/../locales' => base_path('locales')],
			'locale-kit-locales'
		);

		$this->commands([Export::class]);
	}
}