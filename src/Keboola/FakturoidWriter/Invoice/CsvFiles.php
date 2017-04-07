<?php

namespace Keboola\FakturoidWriter\Invoice;

use Keboola\Csv\CsvFile;
use Keboola\FakturoidWriter\UserException;
use Symfony\Component\Filesystem\Filesystem;

class CsvFiles
{
    const FILE_INVOICE = 'invoice.csv';

    const FILE_INVOICE_ITEMS = 'invoice-items.csv';

    private $inputPath;

    private $outputPath;

    private $sourceInvoiceFile;

    private $sourceInvoiceItemsFile;

    public function __construct(string $inputPath, string $outputPath)
    {
        $this->outputPath = $outputPath;
        $this->inputPath = $inputPath;

        if (!(new Filesystem)->exists([
            $this->inputPath . '/' . self::FILE_INVOICE,
            $this->inputPath . '/' . self::FILE_INVOICE_ITEMS
        ])) {
            throw new UserException('Please make sure you provided all required input files.');
        }

        $this->sourceInvoiceFile = new CsvFile($this->inputPath . '/' . self::FILE_INVOICE);
        $this->sourceInvoiceItemsFile = new CsvFile($this->inputPath . '/' . self::FILE_INVOICE_ITEMS);
    }

    public function getSourceInvoiceFile(): CsvFile
    {
        return $this->sourceInvoiceFile;
    }

    public function getSourceInvoiceItemsFile(): CsvFile
    {
        return $this->sourceInvoiceItemsFile;
    }

    public function validate()
    {
        $this->validateItem($this->sourceInvoiceFile, [
            'subject_id',
            'fwr_id',
        ]);

        $this->validateItem($this->sourceInvoiceItemsFile, [
            'fwr_invoice_id',
            'name',
            'quantity',
            'unit_price',
            'unit_price',
            'vat_rate',
        ]);
    }

    private function validateItem(CsvFile $file, array $requiredFields)
    {
        $header = $file->getHeader();
        $diff = array_diff($requiredFields, $header);

        if (!empty($diff)) {
            throw new UserException(
                'Please provide all required fields in '
                . $file->getFilename()
                . ' table. Missing fields: '
                . implode(', ', $diff)
            );
        }

        foreach ($file as $key => $line) {
            if ($key !== 0) {
                $actualFields = array_combine($header, $line);
                foreach ($requiredFields as $field) {
                    if (trim($actualFields[$field]) === '') {
                        throw new UserException('Field ' . $field . ' cannot be empty');
                    }
                }
            }
        }
    }
}
