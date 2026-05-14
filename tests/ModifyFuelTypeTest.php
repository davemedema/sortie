<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyFuelTypeTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      ['Diesel Fuel',   'Diesel'],
      ['Gasoline Fuel', 'Gasoline'],
      ['Flex Fuel',     'Flex'],
      ['Hybrid Fuel',   'Hybrid'],
      ['Foo',           'Gasoline'],
      ['Bar',           'Gasoline'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->fueltype]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}
