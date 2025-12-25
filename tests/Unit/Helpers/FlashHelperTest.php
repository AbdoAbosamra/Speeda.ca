<?php

namespace Tests\Unit\Helpers;

use App\Helpers\FlashHelper;
use Tests\TestCase;

class FlashHelperTest extends TestCase
{
    /** @test */
    public function it_flashes_success_messages()
    {
        FlashHelper::success('Operation completed successfully');

        $this->assertEquals('Operation completed successfully', session('success'));
    }

    /** @test */
    public function it_flashes_success_toast_messages()
    {
        FlashHelper::success('Toast message', true);

        $this->assertEquals('Toast message', session('toast_success'));
        $this->assertNull(session('success'));
    }

    /** @test */
    public function it_flashes_error_messages()
    {
        FlashHelper::error('Something went wrong');

        $this->assertEquals('Something went wrong', session('error'));
    }

    /** @test */
    public function it_flashes_error_toast_messages()
    {
        FlashHelper::error('Error toast', true);

        $this->assertEquals('Error toast', session('toast_error'));
        $this->assertNull(session('error'));
    }

    /** @test */
    public function it_flashes_info_messages()
    {
        FlashHelper::info('Information message');

        $this->assertEquals('Information message', session('info'));
    }

    /** @test */
    public function it_flashes_warning_messages()
    {
        FlashHelper::warning('Warning message');

        $this->assertEquals('Warning message', session('warning'));
    }
}
