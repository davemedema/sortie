<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyTest extends AbstractTestCase
{
  /**
   * test
   */
  #[Group("modify")]
  public function test()
  {
    // Empty property...
    $sortie = new Sortie('[->upper]');

    $actual = $sortie->process(['foo' => 'bar']);

    $this->assertSame('', $actual);

    // Empty modifier...
    $sortie = new Sortie('[foo->]');

    $actual = $sortie->process(['foo' => 'bar']);

    $this->assertSame('', $actual);
  }
}
