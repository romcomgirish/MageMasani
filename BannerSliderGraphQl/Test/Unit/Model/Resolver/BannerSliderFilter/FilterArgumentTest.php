<?php
/**
 * MageMasani BannerSliderGraphQl Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSliderGraphQl
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSliderGraphQl\Test\Unit\Model\Resolver\BannerSliderFilter;

use LogicException;
use MageMasani\BannerSliderGraphQl\Model\Resolver\BannerSliderFilter\FilterArgument;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for FilterArgument
 */
class FilterArgumentTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
    }

    /**
     * Test getEntityAttributes throws LogicException when schema element is missing
     */
    public function testGetEntityAttributesThrowsExceptionWhenTypeMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->configMock->method('getConfigElement')->with('BannerSliderInfo')->willReturn(null);

        $filterArgument = new FilterArgument($this->configMock);
        $filterArgument->getEntityAttributes();
    }

    /**
     * Test getEntityAttributes returns mapped and additional fields
     */
    public function testGetEntityAttributesSuccess(): void
    {
        $field1Mock = $this->createMock(Field::class);
        $field1Mock->method('getName')->willReturn('title');

        $typeMock = $this->createMock(Type::class);
        $typeMock->method('getFields')->willReturn([$field1Mock]);

        $this->configMock->method('getConfigElement')->with('BannerSliderInfo')->willReturn($typeMock);

        $filterArgument = new FilterArgument(
            $this->configMock,
            ['is_enabled', 'start_date'],
            ['title' => 'banner_title']
        );

        $attributes = $filterArgument->getEntityAttributes();
        $this->assertArrayHasKey('title', $attributes);
        $this->assertEquals('banner_title', $attributes['title']['fieldName']);
        $this->assertArrayHasKey('is_enabled', $attributes);
        $this->assertEquals('is_enabled', $attributes['is_enabled']['fieldName']);
    }
}
