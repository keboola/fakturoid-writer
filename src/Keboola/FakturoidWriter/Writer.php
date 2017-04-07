<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Output\OutputInterface;

class Writer
{
    /** @var array */
    private $parameters;

    /** @var OutputInterface */
    private $consoleOutput;

    public function __construct(array $config, OutputInterface $output)
    {
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );

        $this->consoleOutput = $output;
    }

    public function actionRun(string $outputPath): void
    {
    }
}
