<?php declare(strict_types=1);

/**
 * This file is part of ColorSpeaker, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/RGBSpeaker
 *
 * This file is licensed under the MIT License.
 *
 * It is inspired by https://stitcher.io/blog/tests-and-types
 *                   http://archive.is/99WyU
 */

namespace PHPExperts\ColorSpeaker\Tests\internal;

use PHPExperts\ColorSpeaker\DTOs\CSSHexColor;
use PHPExperts\ColorSpeaker\DTOs\HSLColor;
use PHPExperts\ColorSpeaker\internal\RGBSpeaker;
use PHPExperts\ColorSpeaker\Tests\TestHelper;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\ColorSpeaker\DTOs\RGBColor;
use PHPUnit\Framework\TestCase;

/** @testdox PHPExperts\ColorSpeaker\RGBSpeaker */
class RGBSpeakerTest extends TestCase
{
    /** @testdox Can be constructed from an RGBColor */
    public function testCanBeConstructedFromAnRGBColor()
    {
        $rgbColor = new RGBColor([0, 0, 255]);
        $expected = new RGBSpeaker($rgbColor);
        $actual = RGBSpeaker::fromRGB(0, 0, 255);

        self::assertEquals($expected, $actual);
    }

    /** @testdox Can be constructed from a HexColor */
    public function testCanBeConstructedFromAHexColor()
    {
        $rgbColor = new RGBColor([18, 52, 86]);
        $expected = new RGBSpeaker($rgbColor);
        $actual = RGBSpeaker::fromHexCode('#123456');

        self::assertEquals($expected, $actual);
    }

    /** @testdox Can be constructed from an HSLColor */
    public function testCanBeConstructedFromAnHSLColor()
    {
        $colorSets = TestHelper::fetchGoodColorSets();

        foreach ($colorSets as [$cssInfo, $rgbInfo, $hslInfo]) {
            // Test for 0, 0s.
            if ($hslInfo[2] === 0 || $hslInfo[2] === 100) {
                $hslInfo[0] = $hslInfo[1] = 0;
            }

            $rgbColor = new RGBColor($rgbInfo);
            $expected = new RGBSpeaker($rgbColor);
            $actual = RGBSpeaker::fromHSL($hslInfo[0], $hslInfo[1], $hslInfo[2]);

            self::assertEquals($expected, $actual);
        }
    }

    /** @testdox Will only accept integers between 0 and 255, inclusive */
    public function testWillOnlyAcceptIntegersBetween0And255Inclusive()
    {
        $rgb = new RGBSpeaker(new RGBColor([0, 0, 255]));
        self::assertInstanceOf(RGBSpeaker::class, $rgb);

        try {
            new RGBSpeaker(new RGBColor([-1, 5, 256]));
            $this->fail('Created an invalid DTO.');
        } catch (InvalidDataTypeException $e) {
            $expected = [
                'red'  => 'Must be greater than or equal to 0, not -1',
                'blue' => 'Must be lesser than or equal to 255, not 256',
            ];

            self::assertSame('Color values must be between 0 and 255, inclusive.', $e->getMessage());
            self::assertSame($expected, $e->getReasons());
        }
    }

    /** @testdox Can return an RGBColor */
    public function testCanReturnAnRGBColor()
    {
        $expectedDTO = new RGBColor(['red' => 1, 'green' => 1, 'blue' => 1]);
        $rgb = new RGBSpeaker(new RGBColor([1, 1, 1]));
        self::assertEquals($expectedDTO, $rgb->toRGB());
    }

    /** @testdox Can return a CSSHexColor */
    public function testCanReturnACSSHexColor()
    {
        $rgbHexPairs = [
            '#123456' => new RGBColor([ 18,  52,  86]),
            '#803737' => new RGBColor([128,  55,  55]),
            '#374F80' => new RGBColor([ 55,  79, 128]),
            '#398037' => new RGBColor([ 57, 128,  55]),
            '#09EC01' => new RGBColor([  9, 236,   1]),
            '#000099' => new RGBColor([  0,   0, 153]),
        ];

        foreach ($rgbHexPairs as $expected => $rgbDTO) {
            $rgb = new RGBSpeaker($rgbDTO);
            self::assertEquals($expected, $rgb->toHexCode());

            $expectedHexColor = new CSSHexColor($expected);
            self::assertEquals($expectedHexColor, $rgb->toHexCode());
        }
    }

    /** @testdox Can return an HSLColor */
    public function testCanReturnAnHSLColor()
    {
        $colorSets = TestHelper::fetchGoodColorSets();

        foreach ($colorSets as [$cssInfo, $rgbInfo, $hslInfo]) {
            // Test for 0, 0s.
            if ($hslInfo[2] === 0 || $hslInfo[2] === 100) {
                $hslInfo[0] = $hslInfo[1] = 0;
            }

            $expectedDTO = new HSLColor(['hue' => $hslInfo[0], 'saturation' => $hslInfo[1], 'lightness' => $hslInfo[2]]);
            $rgbColor = new RGBColor($rgbInfo);
            $rgb = new RGBSpeaker($rgbColor);

            self::assertEquals($expectedDTO, $rgb->toHSL());
        }
    }

    /** @testdox Can be outputted as a CSS string */
    public function testCanBeOutputtedAsACSSString()
    {
        $expected = 'rgb(127, 127, 127)';
        $rgb = new RGBSpeaker(new RGBColor([127, 127, 127]));
        self::assertEquals($expected, (string) $rgb);
    }
}
