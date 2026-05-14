<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyBodyStyleTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      ['convertible', 'CONVERTIBLE'],
      ['coupe',       'COUPE'],
      ['crossover',   'CROSSOVER'],
      ['hatchback',   'HATCHBACK'],
      ['minivan',     'MINIVAN'],
      ['pickup',      'TRUCK'],
      ['sedan',       'SEDAN'],
      ['suv',         'SUV'],
      ['wagon',       'WAGON'],
      ['van',         'VAN'],
      ['specialty',   'OTHER'],
      ['foo',         'OTHER'],
      ['bar',         'OTHER'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->bodystyle]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}
