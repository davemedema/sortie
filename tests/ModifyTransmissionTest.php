<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyTransmissionTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      ['Automatic', 'automatic'],
      ['Manual', 'manual'],
      ['Foo', 'automatic'],
      ['Bar', 'automatic'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->transmission]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}
