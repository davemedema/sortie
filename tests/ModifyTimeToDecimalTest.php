<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyTimeToDecimalTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      // Invalid...
      ['', ''],
      ['Foo', 'Foo'],
      ['1:2', '1:2'],
      ['1:234', '1:234'],
      ['1:61', '1:61'],
      // Valid...
      [':12', '0.20'],
      ['0:12', '0.20'],
      ['1:01', '1.02'],
      ['1:02', '1.03'],
      ['1:03', '1.05'],
      ['1:04', '1.07'],
      ['1:05', '1.08'],
      ['1:06', '1.10'],
      ['1:07', '1.12'],
      ['1:08', '1.13'],
      ['1:09', '1.15'],
      ['1:10', '1.17'],
      ['1:11', '1.18'],
      ['1:12', '1.20'],
      ['1:13', '1.22'],
      ['1:14', '1.23'],
      ['1:15', '1.25'],
      ['1:30', '1.50'],
      ['1:45', '1.75'],
      ['2:00', '2.00'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->timetodecimal]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}
