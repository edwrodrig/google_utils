<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 22-08-18
 * Time: 16:37
 */

namespace test\edwrodrig\google_utils;

use edwrodrig\google_utils\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{

    public function testRange()
    {
        $this->assertEquals('A1:B2', Util::range('A', 1, 'B', 2));
        $this->assertEquals('A1:AA2', Util::range('A', 1, 'AA', 2));
        $this->assertEquals('A1:B', Util::range('A', 1, 'B'));
    }
}
