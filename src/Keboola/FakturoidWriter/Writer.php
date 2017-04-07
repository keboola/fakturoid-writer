<?php

namespace Keboola\FakturoidWriter;

use Keboola\FakturoidWriter\Invoice\Creator;
use Keboola\FakturoidWriter\Invoice\CsvFiles;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

class Writer
{
    /** @var array */
    private $parameters;

    /** @var OutputInterface */
    private $consoleOutput;

    /** @var string */
    private $inputPath;

    /** @var string */
    private $outputPath;

    /** @var Client */
    private $apiClient;

    public function __construct(array $config, string $inputPath, string $outputPath, OutputInterface $output)
    {
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );

        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;
        $this->consoleOutput = $output;
    }

    public function actionRun(): void
    {
        $invoiceCsvFiles = new CsvFiles($this->inputPath, $this->outputPath);
        $invoiceCsvFiles->validate();

        $requestCreator = new Creator($invoiceCsvFiles);

        foreach ($requestCreator->create() as $body) {
            $this->writeInvoice($body);
        }
    }

    private function writeInvoice(array $body): void
    {
        try {
            $result = $this->getClient()->request('POST', 'invoices.json', [
                'json' => $body
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function getClient(): Client
    {
        if ($this->apiClient === null) {
            $this->apiClient = new Client([
                'base_uri' => 'https://app.fakturoid.cz/api/v2/accounts/' . $this->parameters['slug'] . '/',
                'auth' => [
                    $this->parameters['email'],
                    $this->parameters['#token']
                ],
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Keboola Fakturoid Writer/' . \GuzzleHttp\default_user_agent()
                ],
            ]);
        }

        return $this->apiClient;
    }
}
