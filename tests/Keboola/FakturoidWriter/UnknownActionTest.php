<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UnknownActionTest extends ExtractorTestCase
{
    /** @var string */
    protected $dataDir = '/tmp/unknown-action';

    protected function setUp()
    {
        parent::setUp();

        $this->fs->dumpFile($this->dataDir . '/config.json', <<<JSON
{
  "action": "unknown-action",
  "parameters": {
    "#token": "token",
    "email": "some@email.com",
    "slug": "slug"
  }
}
JSON
        );
    }

    public function testUnknownAction()
    {
        $application = new Application;
        $application->add(new RunCommand);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'command' => $command->getName(),
            'data directory' => $this->dataDir,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertContains('Action "unknown-action" not supported', $commandTester->getDisplay());
    }
}
