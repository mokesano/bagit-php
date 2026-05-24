<?php
declare(strict_types=1);

namespace Wizdam\BagIt\Tests;

use PHPUnit\Framework\TestCase;
use Wizdam\BagIt\BagItManifest;

/**
 * Test class for BagItManifest.
 */
class BagItManifestTest extends TestCase
{
    private string $testDir;
    private string $manifestFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDir = sys_get_temp_dir() . '/bagit_manifest_test_' . uniqid();
        $this->manifestFile = $this->testDir . '/manifest-sha1.txt';
        mkdir($this->testDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->manifestFile)) {
            unlink($this->manifestFile);
        }
        rmdir($this->testDir);
        parent::tearDown();
    }

    public function testCreateManifest(): void
    {
        $manifest = new BagItManifest($this->manifestFile, $this->testDir . '/', 'UTF-8');
        
        $this->assertNotNull($manifest);
        $this->assertEquals('sha1', $manifest->getHashEncoding());
    }

    public function testSetHashEncoding(): void
    {
        $manifest = new BagItManifest($this->manifestFile, $this->testDir . '/', 'UTF-8');
        
        $manifest->setHashEncoding('md5');
        $this->assertEquals('md5', $manifest->getHashEncoding());
    }

    public function testClear(): void
    {
        $manifest = new BagItManifest($this->manifestFile, $this->testDir . '/', 'UTF-8');
        
        $testFile = $this->testDir . '/test.txt';
        file_put_contents($testFile, 'test content');
        
        $manifest->update([$testFile]);
        $manifest->clear();
        
        $this->assertEmpty($manifest->getData());
    }
}
