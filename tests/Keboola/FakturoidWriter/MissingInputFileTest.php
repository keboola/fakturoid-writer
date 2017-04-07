<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MissingInputFileTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/missing-input-file';

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
    }

    public function testMissingDbParameter()
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
            'Please make sure you provided all required input files.',
            $commandTester->getDisplay()
        );
    }

    public function testMissingDbParameterTestMode()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Please make sure you provided all required input files.');

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
