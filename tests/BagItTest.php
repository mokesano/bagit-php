<?php
declare(strict_types=1);

namespace Wizdam\BagIt\Tests;

use PHPUnit\Framework\TestCase;
use Wizdam\BagIt\BagIt;
use Wizdam\BagIt\BagItException;

/**
 * Test class for BagIt.
 */
class BagItTest extends TestCase
{
    private string $testDir;
    private string $bagDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDir = sys_get_temp_dir() . '/bagit_test_' . uniqid();
        $this->bagDir = $this->testDir . '/test_bag';
        mkdir($this->testDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->testDir);
        parent::tearDown();
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testCreateNewBag(): void
    {
        $bag = new BagIt($this->bagDir);
        
        $this->assertNotNull($bag);
        $this->assertTrue(file_exists($this->bagDir . '/bagit.txt'));
        $this->assertTrue(file_exists($this->bagDir . '/data'));
        $this->assertTrue(file_exists($this->bagDir . '/manifest-sha1.txt'));
    }

    public function testBagVersion(): void
    {
        $bag = new BagIt($this->bagDir);
        $info = $bag->getBagInfo();
        
        $this->assertArrayHasKey('version', $info);
        $this->assertMatchesRegularExpression('/^\d+\.\d+$/', $info['version']);
    }

    public function testAddFile(): void
    {
        $bag = new BagIt($this->bagDir);
        
        $testFile = $this->testDir . '/test_content.txt';
        file_put_contents($testFile, 'Test content for bag');
        
        $bag->addFile($testFile, 'data/test_content.txt');
        $bag->update();
        
        $this->assertFileExists($this->bagDir . '/data/test_content.txt');
        $this->assertEquals('Test content for bag', file_get_contents($this->bagDir . '/data/test_content.txt'));
    }

    public function testValidateEmptyBag(): void
    {
        $bag = new BagIt($this->bagDir);
        $errors = $bag->validate();
        
        $this->assertTrue($bag->isValid());
        $this->assertEmpty($errors);
    }

    public function testHashEncoding(): void
    {
        $bag = new BagIt($this->bagDir);
        
        $this->assertEquals('sha1', $bag->getHashEncoding());
        
        $bag->setHashEncoding('md5');
        $this->assertEquals('md5', $bag->getHashEncoding());
    }

    public function testInvalidHashAlgorithm(): void
    {
        $bag = new BagIt($this->bagDir);
        
        $this->expectException(\InvalidArgumentException::class);
        $bag->setHashEncoding('invalid');
    }

    public function testBagInfoData(): void
    {
        $bag = new BagIt($this->bagDir);
        
        $bag->setBagInfoData('Contact-Name', 'Test User');
        $bag->update();
        
        $this->assertTrue($bag->hasBagInfoData('Contact-Name'));
        $this->assertEquals('Test User', $bag->getBagInfoData('Contact-Name'));
    }

    public function testGetDataDirectory(): void
    {
        $bag = new BagIt($this->bagDir);
        
        $expected = $this->bagDir . '/data';
        $this->assertEquals($expected, $bag->getDataDirectory());
    }

    public function testIsExtended(): void
    {
        $bag = new BagIt($this->bagDir, false, true);
        $this->assertTrue($bag->isExtended());
        
        $bag2 = new BagIt($this->bagDir . '2', false, false);
        $this->assertFalse($bag2->isExtended());
    }
}
