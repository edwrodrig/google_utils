<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 22-08-18
 * Time: 16:56
 */

namespace test\edwrodrig\google_utils;

use edwrodrig\google_utils\ScopedArray;
use PHPUnit\Framework\TestCase;

class ScopedArrayTest extends TestCase
{

    public function testEmpty()
    {
        $array = new ScopedArray();
        $this->assertEquals([], $array->getData());

    }

    public function testBasic1()
    {
        $array = new ScopedArray();
        $array['a'] = 1;
        $this->assertEquals(['a' => 1], $array->getData());
        $array['a'] = 2;
        $this->assertEquals(['a' => 2], $array->getData());

        $array['b'] = 3;
        $this->assertEquals(['a' => 2, 'b' => 3], $array->getData());
    }

    public function testEmptyKeys()
    {
        $array = new ScopedArray();
        $array[null] = 1;
        $this->assertEquals([], $array->getData());

        $array['  '] = 1;
        $this->assertEquals([], $array->getData());

    }

    public function testNested1()
    {
        $array = new ScopedArray();
        $array['a.b'] = 1;
        $this->assertEquals(['a' => ['b' => 1]], $array->getData());
        $array['a.b'] = 2;
        $this->assertEquals(['a' => ['b' => 2]], $array->getData());

        $array['b'] = 3;
        $this->assertEquals(['a' => ['b' => 2], 'b' => 3], $array->getData());
    }

    public function testList1()
    {
        $array = new ScopedArray();
        $array['a.0'] = 'A';
        $this->assertEquals(['a' => ['A']], $array->getData());
        $array['a.1'] = 'B';
        $this->assertEquals(['a' => ['A', 'B']], $array->getData());
        $array['a.0'] = 'C';
        $this->assertEquals(['a' => ['C', 'B']], $array->getData());
    }

    public function testNested2()
    {
        $array = new ScopedArray();
        $array['name'] = 'Edwin';
        $array['surname'] = 'Rodriguez';
        $array['mail.user'] = 'edwin';
        $array['mail.domain'] = 'mail.cl';
        $this->assertEquals(['name' => 'Edwin', 'surname' => 'Rodriguez', 'mail' => ['user' => 'edwin', 'domain' => 'mail.cl']], $array->getData());

    }

    public function testArrayFromPairs() {
        $this->assertEquals(
            ['name' => 'Edwin', 'surname' => 'Rodriguez', 'mail' => ['user' => 'edwin', 'domain' => 'mail.cl']],
            ScopedArray::arrayFromPairs([
                ['name', 'Edwin'],
                ['surname', 'Rodriguez'],
                ['mail.user', 'edwin'],
                ['mail.domain', 'mail.cl']
            ])
        );
    }

    public function testArrayFromRows() {
        $this->assertEquals(
            [
                ['name' => 'Edwin', 'surname' => 'Rodriguez'],
                ['name' => 'Amanda', 'surname' => 'Morales']
            ],
            ScopedArray::arrayFromRows(
                ['name', 'surname'],
                [
                    ['Edwin', 'Rodriguez'],
                    ['Amanda', 'Morales']
                ]
            )
        );
    }
}
