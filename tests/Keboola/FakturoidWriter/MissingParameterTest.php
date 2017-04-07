<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MissingParameterTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/missing-parameter';

    protected function setUp()
    {
        parent::setUp();

        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "parameters": {
    "#token": "token",
    "slug": "slug"
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
            'The child node "email" at path "parameters" must be configured.',
            $commandTester->getDisplay()
        );
    }

    public function testMissingDbParameterTestMode()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child node "email" at path "parameters" must be configured.');

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
