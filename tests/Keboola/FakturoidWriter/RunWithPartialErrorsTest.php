<?php

namespace Keboola\FakturoidWriter;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as GuzzleClient;
use Keboola\FakturoidWriter\Invoice\CsvFiles;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RunWithPartialErrorsTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/run-test-partial-errors';

    protected function setUp()
    {
        parent::setUp();

        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "parameters": {
    "#token": "token",
    "slug": "slug",
    "email": "email"
  }
}
JSON
        );

        // content of next 2 files doesn't matter
        $this->fs->dumpFile($this->dataDir . '/in/tables/invoice.csv', <<<CSV
"fwr_id","fwr_order","subject_id"
"10","1","1000"
"20","2","1001"
"30","3","1002"
CSV
        );
        $this->fs->dumpFile($this->dataDir . '/in/tables/invoice-items.csv', <<<CSV
"fwr_invoice_id","name","quantity","unit_price","vat_rate"
CSV
        );
    }

    public function testRun()
    {
        $mock = new MockHandler([
            // first invoice
            new Response(201, [], <<<JSON
{"id":3701,"number":"2017-0001","subject_id":1000,"items":[{"name":"Item 1"}]}
JSON
            ),
            // error
            new Response(422, [], <<<JSON
{"errors":{"exchange_rate":["není číslo","je povinná položka"]}}
JSON
            ),
            // second invoice
            new Response(201, [], <<<JSON
{"id":3702,"number":"2017-0002","subject_id":1001,"items":[{"name":"Item 1"},{"name":"Item 2"}]}
JSON
            ),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handler]);

        $application = new Application;
        $application->add(new RunCommand);

        /** @var RunCommand $command */
        $command = $application->find('run');
        $command->setApiClient($guzzleClient);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $this->assertSame(1, $exitCode);

        $expectedConsoleOutput = <<<TXT
Client error: `POST invoices.json` resulted in a `422 Unprocessable Entity` response
debug: {"errors":{"exchange_rate":["není číslo","je povinná položka"]}}
Processing done.
Number of errors: 1\n
TXT;
        $this->assertSame($expectedConsoleOutput, $commandTester->getDisplay());

        $expectedFile = $this->dataDir . '/out/tables/' . CsvFiles::FILE_FAKTUROID_INVOICE;
        $this->assertFileExists($expectedFile);

        $expectedContent = <<<CSV
"data"
"{""id"":3701,""number"":""2017-0001"",""subject_id"":1000,""items"":[{""name"":""Item 1""}]}"
"{""id"":3702,""number"":""2017-0002"",""subject_id"":1001,""items"":[{""name"":""Item 1""},{""name"":""Item 2""}]}"\n
CSV;

        $this->assertEquals($expectedContent, file_get_contents($expectedFile));
    }
}
