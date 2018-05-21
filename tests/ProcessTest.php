<?php
namespace Tests;

use Sortie\Sortie;
use Tests\AbstractTestCase;

class ProcessTest extends AbstractTestCase
{
  /**
   * test
   *
   * @group process
   */
  public function test()
  {
    // Empty...
    $sortie = new Sortie('');

    $actual = $sortie->process(['foo' => 'Foo']);

    $this->assertEmpty($actual);

    // Simple...
    $sortie = new Sortie('[foo]');

    $actual = $sortie->process(['foo' => 'oof']);

    $this->assertSame('oof', $actual);

    // Complex. The final expression checks the break procedure when an option
    // is matched while there are still other options after it.
    $sortie = new Sortie('[foo] bar [baz] [gamma|alpha|beta]');

    $actual = $sortie->process([
      'alpha' => 'ahpla',
      'beta'  => 'ateb',
      'baz'   => 'zab',
      'foo'   => 'oof'
    ]);

    $this->assertSame('oof bar zab ahpla', $actual);

    // No expressions in the field...
    $sortie = new Sortie('foo');

    $actual = $sortie->process(['foo' => 'oof']);

    $this->assertSame('foo', $actual);

    // String fallback...
    $sortie = new Sortie('[foo|bar|"baz"]');

    $actual = $sortie->process([
      'alpha' => 'ahpla',
      'beta'  => 'ateb',
      'gamma' => 'ammag',
    ]);

    $this->assertSame('baz', $actual);

    // String only...
    $sortie = new Sortie('["foo"]');

    $actual = $sortie->process([
      'foo' => 'bar',
    ]);

    $this->assertSame('foo', $actual);

    // Case insensitive...
    $sortie = new Sortie('[foo] [BAR]');

    $actual = $sortie->process([
      'bar' => 'BAR',
      'FOO' => 'foo',
    ]);

    $this->assertSame('foo BAR', $actual);

    // Space before expression property...
    $sortie = new Sortie('[ foo] [bar ] [ baz ]');

    $actual = $sortie->process([
      ' bar' => 'BAR',
      'baz'  => 'BAZ',
      'foo ' => 'FOO',
    ]);

    $this->assertSame('FOO BAR BAZ', $actual);
  }

  /**
   * testBoolean
   *
   * @group process
   */
  public function testBoolean()
  {
    // Simple TRUE...
    $sortie = new Sortie('[if(foo=bar){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAZ',
      'foo' => 'BAZ',
    ]);

    $this->assertSame('TRUE', $actual);

    // Simple FALSE...
    $sortie = new Sortie('[if(foo=bar){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAR',
      'foo' => 'FOO',
    ]);

    $this->assertSame('FALSE', $actual);

    // Simple empty TRUE...
    $sortie = new Sortie('[if(foo=bar){}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAZ',
      'foo' => 'BAZ',
    ]);

    $this->assertSame('', $actual);

    // Simple empty FALSE...
    $sortie = new Sortie('[if(foo=bar){"TRUE"}else{}]');

    $actual = $sortie->process([
      'bar' => 'BAR',
      'foo' => 'FOO',
    ]);

    $this->assertSame('', $actual);

    // Complex TRUE...
    $sortie = new Sortie('[if(foo->lower=bar->snake){alpha|beta->upper}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar'  => 'tes t',
      'beta' => 'test',
      'foo'  => 'TES_T',
    ]);

    $this->assertSame('TEST', $actual);

    // Complex FALSE...
    $sortie = new Sortie('[if(foo->lower=bar->snake){alpha|beta->title}else{gamma->upper}]');

    $actual = $sortie->process([
      'bar'   => 'tes t',
      'beta'  => 'test',
      'foo'   => 'baz',
      'gamma' => 'test',
    ]);

    $this->assertSame('TEST', $actual);

    // Invalid expression condition...
    $sortie = new Sortie('[if(foo){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAZ',
      'foo' => 'BAZ',
    ]);

    $this->assertSame('', $actual);
  }
}
