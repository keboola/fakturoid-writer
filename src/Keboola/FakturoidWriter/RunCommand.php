<?php

namespace Keboola\FakturoidWriter;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class RunCommand extends Command
{
    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Runs writer');
        $this->addArgument('data directory', InputArgument::REQUIRED, 'Data directory');
        $this->addOption('test-mode', null, null, 'Test mode');
    }

    protected function execute(InputInterface $input, OutputInterface $consoleOutput)
    {
        $dataDirectory = $input->getArgument('data directory');
        $testMode = $input->getOption('test-mode');
        $logger = new Logger('app-errors', [new ErrorLogHandler]);

        try {
            $configFile = $dataDirectory . '/config.json';

            if (!file_exists($configFile)) {
                throw new \Exception('Config file not found at path ' . $configFile);
            }

            $inputPath = $dataDirectory . '/in/tables';
            $outputPath = $dataDirectory . '/out/tables';
            (new Filesystem())->mkdir([$inputPath, $outputPath]);

            $jsonDecode = new JsonDecode(true);
            $config = $jsonDecode->decode(
                file_get_contents($configFile),
                JsonEncoder::FORMAT
            );

            $extractor = new Writer($config, $inputPath, $outputPath, $consoleOutput);
            $action = $config['action'] ?? 'run';

            switch ($action) {
                case 'run':
                    $extractor->actionRun();
                    break;
                default:
                    $consoleOutput->writeln('Action "' . $action . '" not supported');
                    break;
            }
            return 0;
        } catch (InvalidConfigurationException | UserException $e) {
            if ($testMode === true) {
                throw $e;
            }
            $consoleOutput->writeln($e->getMessage());
            return 1;
        } catch (\Exception $e) {
            if ($testMode === true) {
                throw $e;
            }
            $logger->error($e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 2;
        }
    }
}
