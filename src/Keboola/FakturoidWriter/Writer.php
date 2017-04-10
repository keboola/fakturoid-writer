<?php

namespace Keboola\FakturoidWriter;

use Keboola\FakturoidWriter\Invoice\Creator;
use Keboola\FakturoidWriter\Invoice\CsvFiles;
use Symfony\Component\Console\Output\OutputInterface;

class Writer
{
    /** @var OutputInterface */
    private $consoleOutput;

    /** @var string */
    private $inputPath;

    /** @var string */
    private $outputPath;

    /** @var Client */
    private $apiClient;

    public function __construct(Client $apiClient, string $inputPath, string $outputPath, OutputInterface $output)
    {
        $this->apiClient = $apiClient;

        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;
        $this->consoleOutput = $output;
    }

    public function actionRun(): void
    {
        $invoiceCsvFiles = new CsvFiles($this->inputPath, $this->outputPath);
        $invoiceCsvFiles->validate();

        $requestCreator = new Creator($invoiceCsvFiles);

        $fakturoidInvoiceFile = $invoiceCsvFiles->getFakturoidInvoiceFile();
        $fakturoidInvoiceFile->writeRow(['data']);

        foreach ($requestCreator->create() as $body) {
            $result = $this->apiClient->getGuzzleClient()->request('POST', 'invoices.json', [
                'json' => $body
            ]);
            $fakturoidInvoiceFile->writeRow([
                $result->getBody()->getContents()
            ]);
        }
    }
}
