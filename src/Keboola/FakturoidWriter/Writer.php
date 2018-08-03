<?php

namespace Keboola\FakturoidWriter;

use GuzzleHttp\Client;
use Keboola\FakturoidWriter\Invoice\Creator;
use Keboola\FakturoidWriter\Invoice\CsvFiles;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Exception\BadResponseException;

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

    private $parameters;

    public function __construct(Client $apiClient, array $parameters, string $inputPath, string $outputPath, OutputInterface $output)
    {
        $this->apiClient = $apiClient;
        $this->parameters = $parameters;
        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;
        $this->consoleOutput = $output;
    }

    public function actionRun(): void
    {
        $invoiceCsvFiles = new CsvFiles($this->inputPath, $this->outputPath);
        $invoiceCsvFiles->validate();

        $invoiceCreator = new Creator($invoiceCsvFiles, $this->parameters['order']);

        $fakturoidInvoiceFile = $invoiceCsvFiles->getFakturoidInvoiceFile();
        $fakturoidInvoiceFile->writeRow(['data']);

        $numOfErrors = 0;
        foreach ($invoiceCreator->create() as $body) {
            try {
                $result = $this->apiClient->request('POST', 'invoices.json', [
                    'json' => $body
                ]);
                $fakturoidInvoiceFile->writeRow([
                    $result->getBody()->getContents()
                ]);
            } catch (BadResponseException $e) {
                $numOfErrors++;
                $this->consoleOutput->writeln($e->getMessage());
                $this->consoleOutput->writeln((string) $e->getResponse()->getBody());
            }
        }
        $this->consoleOutput->writeln('Processing done. Number of errors: ' . $numOfErrors);
    }
}
