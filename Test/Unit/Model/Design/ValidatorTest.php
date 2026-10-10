<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Design;

use Iranimij\OpenLabel\Model\Design;
use Iranimij\OpenLabel\Model\Design\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private ObjectManager $objectManager;
    private Validator $validator;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->validator = new Validator();
    }

    public function testValidTextDesignHasNoErrors(): void
    {
        self::assertSame([], $this->validator->validate($this->design()));
    }

    public function testValidImageDesignHasNoErrors(): void
    {
        $design = $this->design(['type' => 'image', 'image_path' => 'designs/sale.svg'], ['text' => null, 'alt_text' => 'Sale']);

        self::assertSame([], $this->validator->validate($design));
    }

    /**
     * @dataProvider invalidDesigns
     * @param array<string, mixed> $data
     * @param array<string, mixed> $storeText
     */
    #[DataProvider('invalidDesigns')]
    public function testInvalidFieldsProduceOneMessageEach(array $data, array $storeText, string $message): void
    {
        $design = $this->design($data, $storeText);

        self::assertSame([$message], array_map('strval', $this->validator->validate($design)));
    }

    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>, string}>
     */
    public static function invalidDesigns(): array
    {
        return [
            'name' => [['name' => ''], [], 'Enter a name for the design.'],
            'type' => [['type' => 'video'], [], 'Design type must be text, image or shape.'],
            'text required' => [[], ['text' => ' '], 'Enter the label text for the default store view.'],
            'image required' => [['type' => 'image', 'image_path' => null], ['alt_text' => 'x'], 'Upload an image for this design.'],
            'alt required' => [['type' => 'image', 'image_path' => 'a.png'], ['alt_text' => ''], 'Enter alternative text for the image.'],
            'shape' => [['type' => 'shape', 'shape' => 'star'], [], 'Shape must be rectangle, circle, ribbon, corner or pill.'],
            'bg colour' => [['bg_color' => 'red'], [], 'Colours must be hex values such as #e11d48.'],
            'text colour' => [['text_color' => '#12345'], [], 'Colours must be hex values such as #e11d48.'],
            'size mode' => [['size_mode' => 'em'], [], 'Size mode must be percent or px.'],
            'percent too big' => [['size_mode' => 'percent', 'width' => 101], [], 'Width must be between 1 and 100 percent.'],
            'px too small' => [['size_mode' => 'px', 'width' => 7], [], 'Width must be between 8 and 600 px.'],
            'opacity' => [['opacity' => 101], [], 'Opacity must be between 0 and 100.'],
        ];
    }

    public function testShapeIsOptionalForTextDesigns(): void
    {
        self::assertSame([], $this->validator->validate($this->design(['shape' => null])));
    }

    /**
     * @param array<string, mixed> $overrides
     * @param array<string, mixed> $storeText
     */
    private function design(array $overrides = [], array $storeText = []): Design
    {
        /** @var Design $design */
        $design = $this->objectManager->getObject(Design::class);
        $design->setData(array_merge([
            'name' => 'Red pill',
            'type' => 'text',
            'shape' => 'pill',
            'image_path' => null,
            'bg_color' => '#e11d48',
            'text_color' => '#ffffff',
            'border_color' => null,
            'size_mode' => 'percent',
            'width' => 18,
            'opacity' => 100,
        ], $overrides));
        $storeText = array_merge(['text' => 'Sale', 'alt_text' => null], $storeText);
        $design->setText($storeText['text']);
        $design->setAltText($storeText['alt_text']);

        return $design;
    }
}
