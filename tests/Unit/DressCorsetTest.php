<?php

namespace Tests\Unit;

use App\Models\DressCorset;
use PHPUnit\Framework\TestCase;

class DressCorsetTest extends TestCase
{
    public function test_exposes_the_new_corset_field_names(): void
    {
        $fields = DressCorset::dataFieldNames();

        $this->assertContains('circonferenza_seno', $fields);
        $this->assertContains('linea_sotto_seno', $fields);
        $this->assertContains('ripresa_vita_davanti', $fields);
        $this->assertContains('ripresa_fianchi_dietro', $fields);
        $this->assertNotContains('pinza_vita_davanti', $fields);
    }

    public function test_calculates_larghezza_seno_for_supported_ranges(): void
    {
        $this->assertSame('1/4 CS - 4', DressCorset::larghezzaSenoFormulaFor(96.0));
        $this->assertSame(20.0, DressCorset::calculateLarghezzaSeno(96.0));

        $this->assertSame('1/4 CS - 5', DressCorset::larghezzaSenoFormulaFor(108.0));
        $this->assertSame(22.0, DressCorset::calculateLarghezzaSeno(108.0));
    }

    public function test_returns_null_for_unsupported_larghezza_seno_ranges(): void
    {
        $this->assertNull(DressCorset::larghezzaSenoFormulaFor(79.9));
        $this->assertNull(DressCorset::calculateLarghezzaSeno(120.0));
    }

    public function test_calculates_suggested_linea_sotto_seno(): void
    {
        $this->assertSame(7.6, DressCorset::calculateLineaSottoSenoSuggerita(96.0));
        $this->assertSame(8.3, DressCorset::calculateLineaSottoSenoSuggerita(108.0));
    }

    public function test_calculates_riprese_vita(): void
    {
        $result = DressCorset::calculateRipreseVita(90.0, 70.0);

        $this->assertSame(10.0, $result['base']);
        $this->assertSame(0.625, $result['davanti']);
        $this->assertSame(2.5, $result['lato']);
        $this->assertSame(0.625, $result['dietro']);
    }

    public function test_calculates_riprese_fianchi(): void
    {
        $result = DressCorset::calculateRipreseFianchi(110.0, 90.0);

        $this->assertSame(10.0, $result['base']);
        $this->assertSame(0.0, $result['davanti']);
        $this->assertSame(2.5, $result['lato']);
        $this->assertSame(1.25, $result['dietro']);
    }
}
