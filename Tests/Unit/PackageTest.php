<?php
declare(strict_types=1);

namespace JvMTECH\FlowPlatformSh\Tests\Unit;

use JvMTECH\FlowPlatformSh\Package;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Tests\UnitTestCase;
use Platformsh\ConfigReader\Config;
use ReflectionException;

class PackageTest extends UnitTestCase
{
    /**
     * @dataProvider envFileContentProvider
     * @param scalar $value
     * @throws ReflectionException
     */
    public function testWithProvider(string $envContent, string $variable, string $key, mixed $value): void
    {
        $mock = $this->getMockBuilder(Package::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPlatformShConfig', 'getEnvFileContent', 'assignEnvVariable'])
            ->getMock();
        $platformShConfig_property = new \ReflectionProperty($mock, 'platformShConfig');
        $platformShConfig_property->setValue($mock, $this->getConfigMock($key, $value));

        $mock->method('getEnvFileContent')->willReturn($envContent);
        $mock->expects($this->once())
            ->method('assignEnvVariable')
            ->with($variable, $value);
        $mock->boot($this->getMockBuilder(Bootstrap::class)->disableOriginalConstructor()->getMock());

    }

    /**
     * @param scalar $value
     */
    protected function getConfigMock(string $key, mixed $value): Config
    {
        $mock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('inRuntime')->willReturn(true);
        $mock->method('variable')->willReturn($value);
        $mock->method('credentials')->willReturn([$key => $value]);
        $mock->method('__isset')->willReturn(true);
        $mock->method('__get')->with($key)->willReturn($value);

        return $mock;
    }

    /** @return scalar[][] */
    public function envFileContentProvider(): array
    {
        return [
            ['HOST=rel.host', 'HOST', 'host', 'value'],
            ['HOST=variable.host', 'HOST', 'host', 'value'],
            ['HOST=platform.string', 'HOST', 'string', 'value'],
            ['HOST=platform.int', 'HOST', 'int', 1000],
            ['HOST=platform.bool', 'HOST', 'bool', true],
            ['HOST=platform.float', 'HOST', 'float', 3.1],
        ];
    }
}
