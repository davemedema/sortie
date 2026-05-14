<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ProcessTest extends AbstractTestCase
{
  /**
   * test
   */
  #[Group("process")]
  public function test()
  {
    // Empty...
    $sortie = new Sortie('');

    $actual = $sortie->process(['foo' => 'Foo'], true);

    $this->assertEmpty($actual);

    // Simple...
    $sortie = new Sortie('[foo]');

    $actual = $sortie->process(['foo' => 'oof'], true);

    $this->assertSame('oof', $actual);

    // Complex. The final expression checks the break procedure when an option
    // is matched while there are still other options after it.
    $sortie = new Sortie('[foo] bar [baz] [gamma|alpha|beta]');

    $actual = $sortie->process([
      'alpha' => 'ahpla',
      'beta'  => 'ateb',
      'baz'   => 'zab',
      'foo'   => 'oof'
    ], true);

    $this->assertSame('oof bar zab ahpla', $actual);

    // No expressions in the field...
    $sortie = new Sortie('foo');

    $actual = $sortie->process(['foo' => 'oof'], true);

    $this->assertSame('foo', $actual);

    // String fallback...
    $sortie = new Sortie('[foo|bar|"baz"]');

    $actual = $sortie->process([
      'alpha' => 'ahpla',
      'beta'  => 'ateb',
      'gamma' => 'ammag',
    ], true);

    $this->assertSame('baz', $actual);

    // String only...
    $sortie = new Sortie('["foo"]');

    $actual = $sortie->process(['foo' => 'bar', true]);

    $this->assertSame('foo', $actual);

    // Case insensitive...
    $sortie = new Sortie('[foo] [BAR]');

    $actual = $sortie->process([
      'bar' => 'BAR',
      'FOO' => 'foo',
    ], true);

    $this->assertSame('foo BAR', $actual);

    // Space before expression property...
    $sortie = new Sortie('[ foo] [bar ] [ baz ]');

    $actual = $sortie->process([
      ' bar' => 'BAR',
      'baz'  => 'BAZ',
      'foo ' => 'FOO',
    ], true);

    $this->assertSame('FOO BAR BAZ', $actual);

    // Slashes...
    $sortie = new Sortie('[Foo/Bar\Baz]');

    $actual = $sortie->process([
      'Foo/Bar\Baz' => 'FOOBARBAZ',
    ], true);

    $this->assertSame('FOOBARBAZ', $actual);

    // Parentheses...
    $sortie = new Sortie('[Foo %FLP%Bar%FRP%]');

    $actual = $sortie->process([
      'Foo (Bar)' => 'FOOBAR',
    ], true);

    $this->assertSame('FOOBAR', $actual);
  }

  /**
   * testBoolean
   */
  #[Group("process")]
  public function testBoolean()
  {
    // Simple TRUE...
    $sortie = new Sortie('[if(foo=bar){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAZ',
      'foo' => 'BAZ',
    ], true);

    $this->assertSame('TRUE', $actual);

    // Simple FALSE...
    $sortie = new Sortie('[if(foo=bar){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAR',
      'foo' => 'FOO',
    ], true);

    $this->assertSame('FALSE', $actual);

    // Simple empty TRUE...
    $sortie = new Sortie('[if(foo=bar){}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAZ',
      'foo' => 'BAZ',
    ], true);

    $this->assertSame('', $actual);

    // Simple empty FALSE...
    $sortie = new Sortie('[if(foo=bar){"TRUE"}else{}]');

    $actual = $sortie->process([
      'bar' => 'BAR',
      'foo' => 'FOO',
    ], true);

    $this->assertSame('', $actual);

    // Complex TRUE...
    $sortie = new Sortie('[if(foo->lower=bar->snake){alpha|beta->upper}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar'  => 'tes t',
      'beta' => 'test',
      'foo'  => 'TES_T',
    ], true);

    $this->assertSame('TEST', $actual);

    // Complex FALSE...
    $sortie = new Sortie('[if(foo->lower=bar->snake){alpha|beta->title}else{gamma->upper}]');

    $actual = $sortie->process([
      'bar'   => 'tes t',
      'beta'  => 'test',
      'foo'   => 'baz',
      'gamma' => 'test',
    ], true);

    $this->assertSame('TEST', $actual);

    // Invalid expression condition...
    $sortie = new Sortie('[if(foo){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'bar' => 'BAZ',
      'foo' => 'BAZ',
    ], true);

    $this->assertSame('', $actual);

    // Static string in condition...
    $sortie = new Sortie('[if(foo->lower="bar"){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'foo' => 'BAR',
    ], true);

    $this->assertSame('TRUE', $actual);

    // Static string in condition...
    $sortie = new Sortie('[if(foo->replace:\'/^%LP%c%PI%cc%PI%cct%RP%%LB%0-9%RB%+$/i\':\'MATCH\'="MATCH"){"TRUE"}else{"FALSE"}]');

    $actual = $sortie->process([
      'foo' => 'C90689',
    ], true);

    $this->assertSame('TRUE', $actual);

    // ---
    $sortie = new Sortie('[if(certified->replace:\'/True/i\':\'MATCH\'="MATCH"){"Certified"}ELSE{condition->title}]');
    $actual = $sortie->process([
      'certified' => 'True',
      'condition' => 'foo',
    ], true);
    $this->assertSame('Certified', $actual);

    // ---
    $sortie = new Sortie('[if(certified->replace:\'/True/i\':\'MATCH\'="MATCH"){"Certified"}ELSE{condition->title}]');
    $actual = $sortie->process([
      'certified' => 'False',
      'condition' => 'new',
    ], true);
    $this->assertSame('New', $actual);
  }
}
