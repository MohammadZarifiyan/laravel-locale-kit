<?php

namespace MohammadZarifiyan\LaravelLocaleKit;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use SplFileInfo;

class LocaleManager
{
	protected array $aliases = [];

	public function __construct(public string $defaultLocale)
	{
		//
	}

	public function alias(string $alias, string $identifier): void
	{
		if (str_contains($alias, '_')) {
			throw new InvalidArgumentException(
				sprintf('The alias [%s] must be a language code.', $alias)
			);
		}

		if (!str_contains($identifier, '_')) {
			throw new InvalidArgumentException(
				sprintf('The identifier [%s] must be a locale identifier.', $identifier)
			);
		}

		$this->aliases[$alias] = $identifier;
	}

	public function aliases(): array
	{
		return $this->aliases;
	}

	public function getIdentifier(string $locale): ?string
	{
		if (str_contains($locale, '_')) {
			return $locale;
		}

		return $this->aliases[$locale] ?? null;
	}

	public function locales(): array
	{
		$langDirectory = lang_path();

		if (!File::exists($langDirectory)) {
			return [$this->defaultLocale];
		}

		$directories = array_map(
			fn (string $directory) => basename($directory),
			File::directories($langDirectory)
		);
		$files = File::files($langDirectory);
		$jsonFiles = collect($files)
			->filter(fn (SplFileInfo $file) => $file->getExtension() === 'json')
			->map(fn (SplFileInfo $file) => $file->getBasename('.json'))
			->all();

		return array_unique([...$directories, ...$jsonFiles]);
	}

	public function definedLocales(): array
	{
		$customFiles = $this->getCustomLocaleDefinitionFiles()
			->map(fn (SplFileInfo $file) => $file->getBasename('.json'))
			->all();
		$predefinedFiles = $this->getPredefinedLocaleDefinitionFiles()
			->map(fn (SplFileInfo $file) => $file->getBasename('.json'))
			->all();

		return array_unique([...$customFiles, ...$predefinedFiles]);
	}

	public function get(string $key, ?string $locale = null)
	{
		$identifier = $this->getIdentifier($locale ?: $this->defaultLocale);

		if (is_null($identifier)) {
			return null;
		}

		$customFiles = $this->getCustomLocaleDefinitionFiles();
		$customValue = $this->findValueInFiles($customFiles, $key, $identifier);

		if (!is_null($customValue)) {
			return $customValue;
		}

		$predefinedFiles = $this->getPredefinedLocaleDefinitionFiles();
		$predefinedValue = $this->findValueInFiles($predefinedFiles, $key, $identifier);

		if (!is_null($predefinedValue)) {
			return $predefinedValue;
		}

		return null;
	}

	protected function getPredefinedLocaleDefinitionFiles(): Collection
	{
		$predefinedFiles = File::files(__DIR__ . '/../locales');

		return collect($predefinedFiles)->filter($this->isLocaleDefinitionFile(...));
	}

	protected function getCustomLocaleDefinitionFiles(): Collection
	{
		$localesDirectory = base_path('locales');
		$customFiles = File::isDirectory($localesDirectory) ? File::files($localesDirectory) : [];

		return collect($customFiles)->filter($this->isLocaleDefinitionFile(...));
	}

	protected function getDefinition(SplFileInfo $file): mixed
	{
		$path = $file->getRealPath();
		$contents = File::get($path);

		return json_decode($contents, true);
	}

	protected function isLocaleDefinitionFile(SplFileInfo $file): bool
	{
		if ($file->getExtension() !== 'json') {
			return false;
		}

		$fileName = $file->getBasename('.json');

		return str_contains($fileName, '_');
	}

	protected function findValueInFiles(Collection $files, string $key, string $identifier)
	{
		return $files->filter(fn (SplFileInfo $file) => $file->getBasename('.json') === $identifier)
			->map(function (SplFileInfo $file) use ($key) {
				$definition = $this->getDefinition($file);

				return Arr::get($definition, $key);
			})
			->filter()
			->first();
	}

	public function definitions(): array
	{
		$result = $this->getPredefinedLocaleDefinitionFiles()
			->mapWithKeys(fn (SplFileInfo $file) => [$file->getBasename('.json') => $this->getDefinition($file)])
			->all();
		$customDefinitions = $this->getCustomLocaleDefinitionFiles()
			->mapWithKeys(fn (SplFileInfo $file) => [$file->getBasename('.json') => $this->getDefinition($file)])
			->all();

		foreach ($customDefinitions as $identifier => $definition) {
			$result[$identifier] = array_replace_recursive($result[$identifier] ?? [], $definition);
		}

		return $result;
	}
}