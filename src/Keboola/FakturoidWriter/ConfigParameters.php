<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Config\Definition\Processor;

class ConfigParameters
{
    private $parameters;

    public function __construct(array $config)
    {
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
