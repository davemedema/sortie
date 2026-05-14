<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyDecimalToTimeTest extends AbstractTestCase
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
      // Valid...
      ['-.20','-0:12'],
      ['-0.20','-0:12'],
      ['-1.23', '-1:14'],
      ['0', '0:00'],
      ['0.0', '0:00'],
      ['1', '1:00'],
      ['1.', '1:00'],
      ['123', '123:00'],
      ['0.20','0:12'],
      ['1.02','1:01'],
      ['1.03','1:02'],
      ['1.05','1:03'],
      ['1.07','1:04'],
      ['1.08','1:05'],
      ['1.10','1:06'],
      ['1.12','1:07'],
      ['1.13','1:08'],
      ['1.15','1:09'],
      ['1.17','1:10'],
      ['1.18','1:11'],
      ['1.20','1:12'],
      ['1.22','1:13'],
      ['1.23','1:14'],
      ['1.25','1:15'],
      ['1.50','1:30'],
      ['1.75','1:45'],
      ['2.00','2:00'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->decimaltotime]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}
