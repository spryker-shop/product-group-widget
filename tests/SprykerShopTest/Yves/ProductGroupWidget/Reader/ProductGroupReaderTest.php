<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerShopTest\Yves\ProductGroupWidget\Reader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractGroupStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use ReflectionProperty;
use SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductGroupStorageClientInterface;
use SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductStorageClientInterface;
use SprykerShop\Yves\ProductGroupWidget\Reader\ProductGroupReader;
use SprykerShop\Yves\ProductGroupWidgetExtension\Dependency\Plugin\ProductViewExpanderPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group ProductGroupWidget
 * @group Reader
 * @group ProductGroupReaderTest
 * Add your own group annotations below this line
 */
class ProductGroupReaderTest extends Unit
{
    protected const string LOCALE_NAME = 'de_DE';

    protected const string SUPER_ATTRIBUTE_NAME = 'color';

    protected const string SUPER_ATTRIBUTE_VALUE = 'red';

    protected const int ID_PRODUCT_ABSTRACT = 1;

    protected const int ID_PRODUCT_ABSTRACT_2 = 2;

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(ProductGroupReader::class, 'expandedProductViewTransferCache'))
            ->setValue(null, []);
    }

    public function testGetProductGroupsIndexesSelectedAttributesByEveryGroupMemberId(): void
    {
        // Arrange
        $selectedAttributes = [static::SUPER_ATTRIBUTE_NAME => static::SUPER_ATTRIBUTE_VALUE];
        $groupProductAbstractIds = [static::ID_PRODUCT_ABSTRACT, static::ID_PRODUCT_ABSTRACT_2];

        $productStorageClientMock = $this->createProductStorageClientMock();
        $productStorageClientMock
            ->expects($this->once())
            ->method('getProductAbstractViewTransfers')
            ->with(
                $groupProductAbstractIds,
                static::LOCALE_NAME,
                [
                    static::ID_PRODUCT_ABSTRACT => $selectedAttributes,
                    static::ID_PRODUCT_ABSTRACT_2 => $selectedAttributes,
                ],
            )
            ->willReturn($this->createProductViewTransfers($groupProductAbstractIds));

        $productGroupReader = $this->createProductGroupReader($productStorageClientMock, $groupProductAbstractIds);

        // Act
        $productViewTransfers = $productGroupReader->getProductGroups(
            static::ID_PRODUCT_ABSTRACT,
            static::LOCALE_NAME,
            $selectedAttributes,
        );

        // Assert
        $this->assertCount(2, $productViewTransfers);
    }

    public function testGetProductGroupsPassesEmptySelectedAttributesThrough(): void
    {
        // Arrange
        $groupProductAbstractIds = [static::ID_PRODUCT_ABSTRACT];

        $productStorageClientMock = $this->createProductStorageClientMock();
        $productStorageClientMock
            ->expects($this->once())
            ->method('getProductAbstractViewTransfers')
            ->with($groupProductAbstractIds, static::LOCALE_NAME, [])
            ->willReturn($this->createProductViewTransfers($groupProductAbstractIds));

        $productGroupReader = $this->createProductGroupReader($productStorageClientMock, $groupProductAbstractIds);

        // Act
        $productViewTransfers = $productGroupReader->getProductGroups(static::ID_PRODUCT_ABSTRACT, static::LOCALE_NAME);

        // Assert
        $this->assertCount(1, $productViewTransfers);
    }

    public function testGetProductGroupsExpandsEveryReturnedProductViewTransferOnRepeatedCall(): void
    {
        // Arrange
        $selectedAttributes = [static::SUPER_ATTRIBUTE_NAME => static::SUPER_ATTRIBUTE_VALUE];
        $groupProductAbstractIds = [static::ID_PRODUCT_ABSTRACT];

        $productStorageClientMock = $this->createProductStorageClientMock();
        $productStorageClientMock
            ->method('getProductAbstractViewTransfers')
            ->willReturnCallback(fn (): array => $this->createProductViewTransfers($groupProductAbstractIds));

        $expandedSku = static::SUPER_ATTRIBUTE_VALUE;
        $productViewExpanderPluginMock = $this->createMock(ProductViewExpanderPluginInterface::class);
        $productViewExpanderPluginMock
            ->method('expand')
            ->willReturnCallback(static function (ProductViewTransfer $productViewTransfer) use ($expandedSku): ProductViewTransfer {
                return $productViewTransfer->setSku($expandedSku);
            });

        $productGroupReader = $this->createProductGroupReader(
            $productStorageClientMock,
            $groupProductAbstractIds,
            [$productViewExpanderPluginMock],
        );

        // Act
        $productGroupReader->getProductGroups(static::ID_PRODUCT_ABSTRACT, static::LOCALE_NAME, $selectedAttributes);
        $productViewTransfers = $productGroupReader->getProductGroups(
            static::ID_PRODUCT_ABSTRACT,
            static::LOCALE_NAME,
            $selectedAttributes,
        );

        // Assert
        $this->assertSame(static::SUPER_ATTRIBUTE_VALUE, $productViewTransfers[0]->getSku());
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $productStorageClientMock
     * @param array<int> $groupProductAbstractIds
     * @param array<\SprykerShop\Yves\ProductGroupWidgetExtension\Dependency\Plugin\ProductViewExpanderPluginInterface> $productViewExpanderPlugins
     *
     * @return \SprykerShop\Yves\ProductGroupWidget\Reader\ProductGroupReader
     */
    protected function createProductGroupReader(
        $productStorageClientMock,
        array $groupProductAbstractIds,
        array $productViewExpanderPlugins = []
    ): ProductGroupReader {
        $productGroupStorageClientMock = $this->createMock(ProductGroupWidgetToProductGroupStorageClientInterface::class);
        $productGroupStorageClientMock
            ->method('findProductGroupItemsByIdProductAbstract')
            ->willReturn(
                (new ProductAbstractGroupStorageTransfer())->setGroupProductAbstractIds($groupProductAbstractIds),
            );

        return new ProductGroupReader(
            $productGroupStorageClientMock,
            $productStorageClientMock,
            $productViewExpanderPlugins,
            [],
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductStorageClientInterface
     */
    protected function createProductStorageClientMock(): ProductGroupWidgetToProductStorageClientInterface
    {
        return $this->createMock(ProductGroupWidgetToProductStorageClientInterface::class);
    }

    /**
     * @param array<int> $groupProductAbstractIds
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected function createProductViewTransfers(array $groupProductAbstractIds): array
    {
        $productViewTransfers = [];
        foreach ($groupProductAbstractIds as $idProductAbstract) {
            $productViewTransfers[] = (new ProductViewTransfer())->setIdProductAbstract($idProductAbstract);
        }

        return $productViewTransfers;
    }
}
