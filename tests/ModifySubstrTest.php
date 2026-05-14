<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifySubstrTest extends AbstractTestCase
{
  /**
   * test
   */
  #[Group("modify-substr")]
  public function test()
  {
    // No parameters...
    $sortie = new Sortie('[foo->substr]');

    $actual = $sortie->process(['foo' => 'FooBarBaz']);

    $this->assertSame('FooBarBaz', $actual);

    // No length...
    $sortie = new Sortie('[foo->substr:3]');

    $actual = $sortie->process(['foo' => 'FooBarBaz']);

    $this->assertSame('BarBaz', $actual);

    // With length...
    $sortie = new Sortie('[foo->substr:3:3]');

    $actual = $sortie->process(['foo' => 'FooBarBaz']);

    $this->assertSame('Bar', $actual);
  }
}
