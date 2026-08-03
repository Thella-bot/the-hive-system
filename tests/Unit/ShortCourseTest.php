<?php

namespace Tests\Unit;

use App\Models\ShortCourse;
use PHPUnit\Framework\TestCase;

class ShortCourseTest extends TestCase
{
    public function test_short_course_can_store_online_delivery_details(): void
    {
        $course = new ShortCourse([
            'title' => 'Online Pastry Basics',
            'description' => 'Learn pastry fundamentals online',
            'type' => 'workshop',
            'duration' => '2 weeks',
            'price' => 150,
            'delivery_mode' => 'online',
            'meeting_platform' => 'Zoom',
            'meeting_link' => 'https://zoom.us/j/123456789',
        ]);

        $this->assertSame('online', $course->delivery_mode);
        $this->assertSame('Zoom', $course->meeting_platform);
        $this->assertSame('https://zoom.us/j/123456789', $course->meeting_link);
    }
}
