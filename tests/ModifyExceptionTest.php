<?php
namespace Tests;

use Sortie\Sortie;

class ModifyExceptionTest extends AbstractTestCase
{
  /**
   * test
   */
  public function test()
  {
    $sortie = new Sortie('[foo->exception]');

    $actual = $sortie->process(['foo' => 'Foo']);

    $this->assertSame('', $actual);
  }
}
