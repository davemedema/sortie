<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyKebabTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      ['Foo Bar Baz', 'foo-bar-baz'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->kebab]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}
