<?php

namespace MohammadZarifiyan\LaravelLocaleKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use MohammadZarifiyan\LaravelLocaleKit\LocaleKit;

class Export extends Command
{
	protected $signature = 'locale-kit:export {--directory: The directory where locale definition files will be exported} {--clean-directory: Clean the directory before storing JSON files}';

	protected $description = 'Export all locale definitions as JSON files.';

	public function handle(): int
	{
		$directory = $this->getDirectory();

		File::ensureDirectoryExists($directory);

		if (!File::isEmptyDirectory($directory) && $this->option('clean-directory')) {
			File::cleanDirectory($directory);
		}

		$meta = [
			'locales' => LocaleKit::locales(),
			'defined_locales' => LocaleKit::definedLocales(),
			'aliases' => LocaleKit::aliases(),
		];
		$metaFilePath = $directory . DIRECTORY_SEPARATOR . 'meta.json';
		$savedMeta = File::put($metaFilePath, json_encode($meta));

		if ($savedMeta) {
			$this->info(sprintf('Meta file saved to "%s".', $metaFilePath));
		}
		else {
			$this->error(sprintf('Meta file could not be saved to "%s".', $metaFilePath));

			return Command::FAILURE;
		}

		foreach (LocaleKit::definitions() as $identifier => $definition) {
			$dottedDefinition = Arr::dot($definition);
			$definitionPath = $directory . DIRECTORY_SEPARATOR . $identifier . '.json';
			$savedDefinition = File::put($definitionPath, json_encode($dottedDefinition));

			if ($savedDefinition) {
				$this->info(sprintf('Locale definition "%s" saved to "%s".', $identifier, $definitionPath));
			}
			else {
				$this->error(sprintf('Locale definition "%s" could not be saved to "%s".', $identifier, $definitionPath));

				return Command::FAILURE;
			}
		}

		return Command::SUCCESS;
	}

	protected function getDirectory(): string
	{
		$directory = $this->option('directory');

		return $directory ? base_path($directory) : resource_path('locales');
	}
}