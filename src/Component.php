<?php

declare(strict_types=1);

namespace Keboola\CreateEmptyTablesProcessor;

use Keboola\Component\BaseComponent;
use Keboola\Component\Manifest\ManifestManager\Options\OutTableManifestOptions;
use Keboola\Csv\CsvWriter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class Component extends BaseComponent
{
    protected function run(): void
    {
        $inputDir = $this->getDataDir() . '/in';
        $outputDir = $this->getDataDir() . '/out';

        $this->copyFiles($inputDir, $outputDir);

        foreach ($this->getConfig()->getTables() as $table) {
            $filename = sprintf('%s/tables/%s', $outputDir, $table['tableName']);

            if (file_exists($filename) || file_exists($filename . '.csv')) {
                $this->getLogger()->info(sprintf('Table "%s" is exists. Skipping.', $table['tableName']));
                continue;
            }

            $csv = new CsvWriter($filename . '.csv');
            $csv->writeRow($table['columns']);

            $this->createManifest($table['tableName'], $table['columns']);

            $this->getLogger()->info(sprintf('Adding empty table "%s".', $table['tableName']));
        }
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    public function getConfig(): Config
    {
        /** @var Config $config */
        $config = parent::getConfig();
        return $config;
    }

    private function copyFiles(string $inputDir, string $outputDir): void
    {
        $fs = new Filesystem();
        $fs->mkdir($outputDir);

        if ($fs->exists($inputDir)) {
            $finder = new Finder();
            $count = $finder->in($inputDir)->depth(0)->count();
            if ($count > 0) {
                Process::fromShellCommandline(
                    sprintf(
                        'cp -R %s/* %s/',
                        $inputDir,
                        $outputDir
                    )
                )->mustRun();
            }
        }
    }

    private function createManifest(string $tableName, array $columns): void
    {
        $manifestManager = $this->getManifestManager();

        $outTableManifestOptions = new OutTableManifestOptions();
        $outTableManifestOptions->setColumns($columns);
        $outTableManifestOptions->setIncremental(false);
        $outTableManifestOptions->setDelimiter(',');
        $outTableManifestOptions->setEnclosure('"');
        $outTableManifestOptions->setPrimaryKeyColumns([]);

        $manifestManager->writeTableManifest($tableName . '.csv', $outTableManifestOptions);
    }
}
