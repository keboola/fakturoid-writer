<?php

namespace Keboola\FakturoidWriter\Unit;

use Keboola\FakturoidWriter\Invoice\Creator;
use Keboola\FakturoidWriter\Invoice\CsvFiles;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class InvoiceCreatorDescTest extends TestCase
{
    /** @var string */
    protected $dataDir = '/tmp/invoice-creator-test-desc';

    private $fs;

    protected function setUp()
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->dataDir);
        $this->fs->mkdir($this->dataDir);

        $this->fs->dumpFile($this->dataDir . '/in/tables/invoice.csv', <<<CSV
"fwr_id","fwr_order","subject_id"
"10","2","1000"
"20","1","1001"
CSV
        );
        $this->fs->dumpFile($this->dataDir . '/in/tables/invoice-items.csv', <<<CSV
"fwr_invoice_id","name","quantity","unit_price","vat_rate"
"10","item 1","1","10","0"
"20","item 1","2","10","0"
"20","item 2","3","20","0"
CSV
        );
    }

    protected function tearDown()
    {
        $this->fs->remove($this->dataDir);
    }

    public function testCreate()
    {
        $csvFiles = new CsvFiles($this->dataDir . '/in/tables', $this->dataDir . '/out/tables');
        $csvFiles->validate();

        $creator = new Creator($csvFiles, 'desc');
        $bodies = $creator->create();

        $expectedJson = <<<JSON
{
    "2": {
        "subject_id": "1000",
        "lines": [
            {
                "name": "item 1",
                "quantity": "1",
                "unit_price": "10",
                "vat_rate": "0"
            }
        ]
    },
    "1": {
        "subject_id": "1001",
        "lines": [
            {
                "name": "item 1",
                "quantity": "2",
                "unit_price": "10",
                "vat_rate": "0"
            },
            {
                "name": "item 2",
                "quantity": "3",
                "unit_price": "20",
                "vat_rate": "0"
            }
        ]
    }
}
JSON;

        $this->assertEquals($expectedJson, \json_encode($bodies, JSON_PRETTY_PRINT));
    }
}
