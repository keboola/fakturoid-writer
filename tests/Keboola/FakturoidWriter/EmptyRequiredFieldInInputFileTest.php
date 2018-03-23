<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class EmptyRequiredFieldInInputFileTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/empty-required-field-in-input-file';

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

        $this->fs->dumpFile($this->dataDir . '/in/tables/invoice.csv', <<<CSV
"fwr_id","fwr_order","subject_id"
"1","1",""
CSV
        );
        $this->fs->dumpFile($this->dataDir . '/in/tables/invoice-items.csv', <<<CSV
"fwr_invoice_id","name","quantity","unit_price","vat_rate"
"1","name","1","10","0"
CSV
        );
    }

    public function testMissingRequiredFieldInInputFile()
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertContains(
            'Field subject_id in invoice.csv file cannot be empty',
            $commandTester->getDisplay()
        );
    }

    public function testMissingRequiredFieldInInputFileTestMode()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage(
            'Field subject_id in invoice.csv file cannot be empty'
        );

        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
            '--test-mode' => true,
        ]);
    }
}
