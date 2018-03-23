<?php

namespace Keboola\FakturoidWriter\Invoice;

class Creator
{
    private $csvFiles;

    private $sort;

    public function __construct(CsvFiles $csvFiles, string $sort)
    {
        $this->csvFiles = $csvFiles;
        $this->sort = $sort;
    }

    public function create(): array
    {
        $invoiceFileHeader = $this->csvFiles->getSourceInvoiceFile()->getHeader();
        $invoiceItemFileHeader = $this->csvFiles->getSourceInvoiceItemsFile()->getHeader();

        $invoices = [];
        $invoiceIdAndOrder = [];

        foreach ($this->csvFiles->getSourceInvoiceFile() as $i => $invoice) {
            if ($i !== 0) {
                $invoiceValue = array_combine($invoiceFileHeader, $invoice);
                $fwrInvoiceId = $invoiceValue['fwr_id'];
                $fwrOrder = $invoiceValue['fwr_order'];
                $invoiceIdAndOrder[$fwrOrder] = $fwrInvoiceId;
                unset($invoiceValue['fwr_id']);
                unset($invoiceValue['fwr_order']);
                $invoices[$fwrInvoiceId] = $invoiceValue;
            }
        }

        foreach ($this->csvFiles->getSourceInvoiceItemsFile() as $j => $invoiceItem) {
            if ($j !== 0) {
                $invoiceItemValue = array_combine($invoiceItemFileHeader, $invoiceItem);
                $fwrInvoiceId = $invoiceItemValue['fwr_invoice_id'];
                unset($invoiceItemValue['fwr_invoice_id']);
                $invoices[$fwrInvoiceId]['lines'][] = $invoiceItemValue;
            }
        }

        $bodies = [];

        foreach ($invoiceIdAndOrder as $order => $invoiceId) {
            $bodies[$order] = $invoices[$invoiceId];
        }

        $this->sort === 'asc' ? ksort($bodies) : krsort($bodies);

        return $bodies;
    }
}
