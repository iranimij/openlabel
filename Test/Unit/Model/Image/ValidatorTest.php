<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Image;

use Iranimij\OpenLabel\Model\Image\SvgSanitizer;
use Iranimij\OpenLabel\Model\Image\Validator;
use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var string[] */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function file(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'olimg');
        file_put_contents($path, $contents);
        $this->files[] = $path;

        return $path;
    }

    /** A real 2 × 3 px PNG (GD is a Magento requirement). */
    private function png(): string
    {
        $image = imagecreatetruecolor(2, 3);
        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    private function validator(): Validator
    {
        return new Validator(new SvgSanitizer(), new File());
    }

    public function testPngIsAcceptedWithItsDimensions(): void
    {
        $result = $this->validator()->validate($this->file($this->png()), 'sale.png');

        self::assertSame([], $result->getErrors());
        self::assertSame('png', $result->getExtension());
        self::assertSame(2, $result->getWidth());
        self::assertSame(3, $result->getHeight());
        self::assertSame([], $result->getWarnings());
    }

    public function testSvgIsAcceptedAndSanitized(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="24" onload="x()"><rect width="80" height="24"/></svg>';

        $result = $this->validator()->validate($this->file($svg), 'Badge.SVG');

        self::assertSame([], $result->getErrors());
        self::assertSame('svg', $result->getExtension());
        self::assertSame([80, 24], [$result->getWidth(), $result->getHeight()]);
        self::assertStringNotContainsString('onload', (string) $result->getSanitizedContents());
    }

    public function testPhpDisguisedAsPngIsRejected(): void
    {
        $result = $this->validator()->validate($this->file('<?php echo "x";'), 'shell.png');

        self::assertSame(['The file content does not match its extension. Upload a real PNG, JPG, GIF, WebP or SVG image.'], array_map('strval', $result->getErrors()));
    }

    public function testUnsupportedExtensionIsRejected(): void
    {
        $result = $this->validator()->validate($this->file('x'), 'label.php');

        self::assertSame(['Allowed image types are PNG, JPG, GIF, WebP and SVG.'], array_map('strval', $result->getErrors()));
    }

    public function testFilesOver50KbGetAWarningAndOver1MbAnError(): void
    {
        $png = $this->png();

        $big = $this->validator()->validate($this->file($png . str_repeat("\0", 60 * 1024)), 'big.png');
        self::assertSame([], $big->getErrors());
        self::assertStringContainsString('larger than 50 KB', (string) ($big->getWarnings()[0] ?? ''));

        $huge = $this->validator()->validate($this->file($png . str_repeat("\0", 1100 * 1024)), 'huge.png');
        self::assertStringContainsString('1 MB', (string) ($huge->getErrors()[0] ?? ''));
    }
}
