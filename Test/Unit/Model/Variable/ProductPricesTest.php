<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Variable;

use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Processor\Attr;
use Iranimij\OpenLabel\Model\Variable\ProductPrices;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use PHPUnit\Framework\TestCase;

class ProductPricesTest extends TestCase
{
    public function testCompositesAndProductsWithoutLoadedPricesAskThePriceInfo(): void
    {
        $amount = $this->createStub(AmountInterface::class);
        $amount->method('getValue')->willReturn(42.0);
        $price = $this->createStub(PriceInterface::class);
        $price->method('getAmount')->willReturn($amount);
        $priceInfo = $this->createStub(PriceInfoInterface::class);
        $priceInfo->method('getPrice')->willReturn($price);
        $product = $this->createStub(Product::class);
        $product->method('getTypeId')->willReturn('configurable');
        $product->method('getPriceInfo')->willReturn($priceInfo);
        $product->method('getData')->willReturn(null);
        $prices = new ProductPrices();

        self::assertSame(42.0, $prices->regular($product));
        self::assertSame(42.0, $prices->final($product));
        self::assertNull($prices->special($product));
    }

    public function testForeignProductImplementationsFallBackToGetPrice(): void
    {
        $product = $this->createStub(ProductInterface::class);
        $product->method('getPrice')->willReturn(9.5);
        $prices = new ProductPrices();

        self::assertSame(9.5, $prices->regular($product));
        self::assertSame(9.5, $prices->final($product));
        self::assertNull($prices->special($product));
    }

    public function testAttrUsesOptionLabelsForSelectAttributes(): void
    {
        $attribute = $this->createStub(Attribute::class);
        $attribute->method('getFrontendInput')->willReturn('select');
        $eavConfig = $this->createStub(EavConfig::class);
        $eavConfig->method('getAttribute')->willReturn($attribute);
        $product = $this->createStub(Product::class);
        $product->method('hasData')->willReturn(true);
        $product->method('getData')->willReturn('12');
        $product->method('getAttributeText')->willReturn('Red');

        self::assertSame('Red', (new Attr($eavConfig))->getValue($product, new Context(1, 1, 0), 'color')->raw);
        self::assertTrue((new Attr($eavConfig))->getValue($this->createStub(ProductInterface::class), new Context(1, 1, 0), 'color')->isEmpty());
    }
}
