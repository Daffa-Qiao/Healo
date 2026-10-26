<?php

namespace Tests\Unit;

use App\Services\HealthAI\EmergencyDetectionService;
use PHPUnit\Framework\TestCase;

class EmergencyDetectionServiceTest extends TestCase
{
    public function test_it_detects_physical_and_self_harm_red_flags(): void
    {
        $service = new EmergencyDetectionService;

        $this->assertContains('severe_chest_pain', $service->detect('Saya mengalami nyeri dada hebat sekarang.'));
        $this->assertContains('self_harm_emergency', $service->detect('Saya ingin menyakiti diri dan bunuh diri.'));
        $this->assertSame([], $service->detect('Saya sakit kepala ringan sejak pagi.'));
    }
}
