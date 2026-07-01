<?php

namespace MohammadZarifiyan\LaravelLocaleKit\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MohammadZarifiyan\LaravelLocaleKit\LocaleManager;

class DeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
	public function register(): void
	{
		$this->app->bind(LocaleManager::class , function (Application $application) {
			$defaultLocale = $application->getLocale();

			return new LocaleManager($defaultLocale);
		});
	}

	public function provides(): array
	{
		return [LocaleManager::class];
	}
}