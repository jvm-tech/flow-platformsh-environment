<?php

declare(strict_types=1);

namespace JvMTECH\FlowPlatformSh;

use M1\Env\Parser;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Utility\Arrays;
use Platformsh\ConfigReader\Config;

/** @psalm-api */
class Package extends BasePackage
{
    protected Config $platformShConfig;

    /** This is only extracted to enable testing with mocked Config object */
    protected function setPlatformShConfig(Config $config): void
    {
        $this->platformShConfig = $config;
    }

    public function boot(Bootstrap $bootstrap)
    {
        $this->setPlatformShConfig(new Config());

        if (!$this->platformShConfig->inRuntime()) {
            return;
        }

        $fileContent = $this->getEnvFileContent() ?? '';

        /** @var string $environmentVariable */
        foreach (Parser::parse($fileContent) as $environmentVariable => $placeholder) {
            if (!is_string($placeholder)) {
                continue;
            }
            [$source, $configKey] = explode('.', $placeholder);

            switch ($source) {
                case 'platform':
                    /** @psalm-suppress MixedAssignment */
                    $configValue = $this->platformShConfig->{$configKey};
                    break;
                case 'variable':
                    /** @psalm-suppress MixedAssignment */
                    $configValue = $this->platformShConfig->variable($configKey);
                    break;
                default:
                    /** @psalm-suppress MixedAssignment */
                    $configValue = Arrays::getValueByPath($this->platformShConfig->credentials($source), trim($configKey));
            }

            if (is_scalar($configValue)) {
                $this->assignEnvVariable($environmentVariable, $configValue);
            }
        }
    }

    protected function getEnvFileContent(): string|null
    {
        /** @psalm-suppress UndefinedConstant */
        $envFile = \FLOW_PATH_ROOT . '.platform.env';

        if (!file_exists($envFile)) {
            return null;
        }

        $fileContent = file_get_contents($envFile);
        if (!is_string($fileContent)) {
            return null;
        }
        return $fileContent;
    }

    protected function assignEnvVariable(string $key, bool|float|int|string $value): void
    {
        putenv(trim($key) . "=" . $value);
    }
}
