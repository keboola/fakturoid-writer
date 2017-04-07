<?php

namespace Keboola\FakturoidWriter\Invoice;

class Creator
{
    private $csvFiles;

    public function __construct(CsvFiles $csvFiles)
    {
        $this->csvFiles = $csvFiles;
    }

    public function create(): array
    {
        $bodies = [];
        $invoiceFileHeader = $this->csvFiles->getSourceInvoiceFile()->getHeader();
        $invoiceItemFileHeader = $this->csvFiles->getSourceInvoiceItemsFile()->getHeader();

        foreach ($this->csvFiles->getSourceInvoiceFile() as $i => $invoice) {
            if ($i !== 0) {
                $invoiceVal = array_combine($invoiceFileHeader, $invoice);
                $invoiceId = $invoiceVal['fwr_id'];
                unset($invoiceVal['fwr_id']);
                $bodies[$invoiceId] = $invoiceVal;
            }
        }

        foreach ($this->csvFiles->getSourceInvoiceItemsFile() as $j => $invoiceItem) {
            if ($j !== 0) {
                $invoiceItemVal = array_combine($invoiceItemFileHeader, $invoiceItem);
                $invoiceId = $invoiceItemVal['fwr_invoice_id'];
                unset($invoiceItemVal['fwr_invoice_id']);
                $bodies[$invoiceId]['lines'][] = $invoiceItemVal;
            }
        }

        return $bodies;
    }
}
