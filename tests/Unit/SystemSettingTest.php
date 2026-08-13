<?php

namespace Tests\Unit;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_returns_default_when_key_does_not_exist()
    {
        $value = SystemSetting::get('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $value);
    }

    public function test_set_creates_updates_and_busts_cache()
    {
        // Test create
        SystemSetting::set('test_key', 'value1');
        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_key',
            'value' => json_encode('value1')
        ]);
        $this->assertEquals('value1', SystemSetting::get('test_key'));

        // Test update
        SystemSetting::set('test_key', 'value2');
        
        // Cache should be busted, so it should return value2
        $this->assertEquals('value2', SystemSetting::get('test_key'));
    }

    public function test_all_settings_returns_all_settings_as_array()
    {
        SystemSetting::set('key1', 'value1');
        SystemSetting::set('key2', 'value2');

        $settings = SystemSetting::allSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('key1', $settings);
        $this->assertArrayHasKey('key2', $settings);
        $this->assertEquals('value1', $settings['key1']);
        $this->assertEquals('value2', $settings['key2']);
    }
}
