<?php

namespace App\Tests\Entity;

use App\Entity\Forecast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ForecastTest extends TestCase
{
    #[DataProvider('dataGetFahrenheit')]
    public function testGetFahrenheit(string $celsius, string $expectedFahrenheit): void
    {
        $measurement = new Forecast();
        $measurement->setCelsius((float) $celsius);
        $this->assertEquals((float) $expectedFahrenheit, $measurement->getFahrenheit());
    }
    public static function dataGetFahrenheit(): array
    {
        return [
            ['0', '32'],
            ['-100', '-148'],
            ['100', '212'],
            ['0.5', '32.9'],
            ['-0.5', '31.1'],
            ['37', '98.6'],
            ['-40', '-40'],
            ['20', '68'],
            ['25.5', '77.9'],
            ['-10.3', '13.46'],
        ];
    }
    /*public function testGetFahrenheit(): void
    {
        $measurement = new Forecast();

        // test 0°C → 32°F
        $measurement->setCelsius(0);
        $this->assertEquals(32, $measurement->getFahrenheit());

        // test -100°C → -148°F
        $measurement->setCelsius(-100);
        $this->assertEquals(-148, $measurement->getFahrenheit());

        // test 100°C → 212°F
        $measurement->setCelsius(100);
        $this->assertEquals(212, $measurement->getFahrenheit());
    }*/
}
