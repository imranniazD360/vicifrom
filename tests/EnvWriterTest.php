<?php

declare(strict_types=1);

namespace Viciform\Tests;

use PHPUnit\Framework\TestCase;
use Viciform\Laravel\EnvWriter;

final class EnvWriterTest extends TestCase
{
    /** @var string */
    private $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = sys_get_temp_dir() . '/viciform-env-' . uniqid('', true) . '.env';
        file_put_contents($this->path, "APP_NAME=Test\n");
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
        parent::tearDown();
    }

    public function testEnsureKeysAddsMissingOnly()
    {
        $writer = new EnvWriter($this->path);
        $added = $writer->ensureKeys([
            'VICIFORM_USER' => 'api',
        ]);

        $this->assertContains('VICIFORM_BASE_URL', $added);
        $this->assertContains('VICIFORM_USER', $added);
        $this->assertSame('api', $writer->get('VICIFORM_USER'));

        $addedAgain = $writer->ensureKeys([
            'VICIFORM_USER' => 'other',
        ]);
        $this->assertSame([], $addedAgain);
        $this->assertSame('api', $writer->get('VICIFORM_USER'));
    }

    public function testSetOverwritesAndQuotes()
    {
        $writer = new EnvWriter($this->path);
        $writer->set('VICIFORM_BASE_URL', 'https://dial.example/vicidial/non_agent_api.php');
        $writer->set('VICIFORM_PASS', 'secret value');

        $this->assertSame('https://dial.example/vicidial/non_agent_api.php', $writer->get('VICIFORM_BASE_URL'));
        $this->assertSame('secret value', $writer->get('VICIFORM_PASS'));
    }
}
