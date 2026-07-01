<?php

namespace MohammadZarifiyan\LaravelLocaleKit;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void alias(string $alias, string $identifier)
 * @method static array aliases()
 * @method static string|null getIdentifier(string $locale)
 * @method static array locales()
 * @method static array definedLocales()
 * @method static mixed get(string $key, ?string $locale = null)
 * @method static array definitions()
 *
 * @see LocaleManager
 */
class LocaleKit extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return LocaleManager::class;
	}
}