<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReportCollection\Libs\Collector;

/**
 * Esta implementação permite chamar métodos protegidos como se fosse publicos.
 * Ideal para testes de unidade :)
 */
class ProtectedCollector extends Collector{

    private $instance = null;

    public function __construct()
    {
        $this->instance = self::createFromArray(array(["Company", "Contact", "Country"]));
    }

    public function __call($name, $arguments)
    {
        $args = array_fill(0, 6, true);
        foreach($arguments as $index => $value) {
            $args[$index] = $value;
        }

        if (method_exists($this->instance, $name)) {
            return $this->instance->$name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        }
    }
}

class HelpersTest extends TestCase
{
    public function testCreateColor()
    {
        $handle = new ProtectedCollector;

        $object = $handle->createColor('#ee00ee');

        $this->assertInstanceOf('PhpOffice\PhpSpreadsheet\Style\Color', $object);
        $this->assertEquals('FFEE00EE', $object->getARGB());
        $this->assertEquals('EE00EE', $object->getRGB());
    }

    public function testParseHexCode()
    {
        $handle = new ProtectedCollector;

        // 8 digitos
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#ffffffff'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('ffffffff'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#FFFFFFFF'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('FFFFFFFF'));
        $this->assertEquals('00FFFFFF', $handle->parseHex('00FffFFF'));

        // 6 digitos
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#ffffff'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('ffffff'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#FFFFFF'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('FFFFFF'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('FFffFF'));

        // +3 digitos
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#fff6'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('fff2'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#FFF7'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('FfF9'));

        // 3 digitos
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#fff'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('fff'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('#FFF'));
        $this->assertEquals('FFFFFFFF', $handle->parseHex('FfF'));
    }

    public function testColumnVowel()
    {
        $handle = new ProtectedCollector;

        $alpha = 26; // numero de letras no alfabeto

        $this->assertEquals('A', $handle->getColumnVowel(1));
        $this->assertEquals('Z', $handle->getColumnVowel($alpha));
        $this->assertEquals('AA', $handle->getColumnVowel($alpha + 1));
        $this->assertEquals('AZ', $handle->getColumnVowel($alpha * 2));
        $this->assertEquals('BA', $handle->getColumnVowel($alpha * 2 + 1));
        $this->assertEquals('BZ', $handle->getColumnVowel($alpha * 3));

        $this->assertEquals('A', $handle->getColumnVowel('A'));
        $this->assertEquals('Z', $handle->getColumnVowel('Z'));
        $this->assertEquals('ZA', $handle->getColumnVowel('ZA'));
    }

    public function testColumnNumber()
    {
        $handle = new ProtectedCollector;

        $alpha = 26; // numero de letras no alfabeto

        $this->assertEquals(1, $handle->getColumnNumber('A'));
        $this->assertEquals($alpha, $handle->getColumnNumber('Z'));
        $this->assertEquals($alpha + 1, $handle->getColumnNumber('AA'));
        $this->assertEquals($alpha * 2, $handle->getColumnNumber('AZ'));
        $this->assertEquals($alpha * 2 + 1, $handle->getColumnNumber('BA'));
        $this->assertEquals($alpha * 3, $handle->getColumnNumber('BZ'));

        $this->assertEquals(1, $handle->getColumnNumber(1));
        $this->assertEquals($alpha, $handle->getColumnNumber(26));
        $this->assertEquals(49, $handle->getColumnNumber(49));
    }

    public function testNormalizeStyles()
    {
        $handle = new ProtectedCollector;

        $defaults = $handle->getStyles();
        $normalized = $handle->normalizeStyles([]);

        $this->assertEquals($defaults, $normalized);
        $this->assertEquals(count($defaults), count($normalized));

        $normalized = $handle->normalizeStyles(['color' => '#ffffff']);
        $this->assertNotEquals($defaults['color'], $normalized['color'] );
        $this->assertEquals(count($defaults), count($normalized));
    }
}
