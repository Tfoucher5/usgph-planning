<?php

namespace Tests\Unit;

use Carbon\Carbon;
use NumberFormatter;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_secure_encode64()
    {
        $this->app['config']->set('app.key', 'base64:' . base64_encode('test_key'));

        $result = secureEncode64('test_data');
        $this->assertEquals(
            base64_encode('test_data' . env('APP_KEY')),
            $result
        );
    }

    public function test_format_currency()
    {
        $result = format_currency(1234.56);
        $fmt = new NumberFormatter('fr_FR', NumberFormatter::CURRENCY);
        $this->assertEquals($fmt->formatCurrency(1234.56, 'EUR'), $result);
    }

    public function test_format_number()
    {
        $result = format_number(1234.56);
        $fmt = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $fmt->setPattern('#,##0.00');
        $this->assertEquals($fmt->format(1234.56), $result);
    }

    public function test_format_date()
    {
        $date = '2023-01-01';
        $result = format_date($date);
        $carbonDate = Carbon::parse($date)->locale('fr_FR');
        $this->assertEquals($carbonDate->isoFormat('DD/MM/YYYY'), $result);
    }

    public function test_format_date_null()
    {
        $result = format_date(null);
        $this->assertNull($result);
    }

    public function test_format_hour()
    {
        $result = format_hour('12:34:56');
        $this->assertEquals('12:34', $result);
    }

    public function test_format_hour_null()
    {
        $result = format_hour(null);
        $this->assertNull($result);
    }

    public function test_format_telephone()
    {
        $result = format_telephone('0123456789');
        $this->assertEquals(
            '01 23 45 67 89',
            str_replace("\u{00A0}", ' ', $result)
        );
    }

    public function test_format_telephone_null()
    {
        $result = format_telephone(null);
        $this->assertNull($result);
    }

    public function test_format_siret()
    {
        $result = format_siret('12345678901234');
        $this->assertEquals(
            '123 456 789 01234',
            str_replace("\u{00A0}", ' ', $result)
        );
    }

    public function test_format_siret_null()
    {
        $result = format_siret(null);
        $this->assertNull($result);
    }

    public function test_format_date_fr_to_eng()
    {
        $result = format_date_FrToEng('01/01/2023');
        $this->assertEquals('2023-01-01', $result);
    }

    public function test_format_date_fr_to_eng_null()
    {
        $result = format_date_FrToEng(null);
        $this->assertNull($result);
    }

    public function test_nb_days_between()
    {
        $result = nbDaysBetween('2023-01-01', '2023-01-10');
        $this->assertEquals(9, $result);
    }

    public function test_nb_days_off_between()
    {
        $result = nbDaysOffBetween('2023-01-01', '2023-01-10');
        $this->assertEquals(3, $result);
    }

    public function test_size_file_readable()
    {
        $this->assertEquals('1B', sizeFileReadable(1));
        $this->assertEquals('1kB', sizeFileReadable(1024));
        $this->assertEquals('1MB', sizeFileReadable(1024 * 1024));
        $this->assertEquals('1GB', sizeFileReadable(1024 * 1024 * 1024));
        $this->assertEquals('1TB', sizeFileReadable(1024 * 1024 * 1024 * 1024));

        $this->assertEquals('1023B', sizeFileReadable(1023));
        $this->assertEquals('1kB', sizeFileReadable(1025));
        $this->assertEquals('1024kB', sizeFileReadable(1024 * 1024 - 1));
        $this->assertEquals('1MB', sizeFileReadable(1024 * 1024 + 1));

        $this->assertEquals('1.23TB', sizeFileReadable(1.23 * 1024 * 1024 * 1024 * 1024));

        $this->assertEquals('1.23MB', sizeFileReadable(1.23 * 1024 * 1024));
        $this->assertEquals('1.23GB', sizeFileReadable(1.23 * 1024 * 1024 * 1024));
    }

    public function test_size_file_readablet_null()
    {
        $result = sizeFileReadable(0.1);
        $this->assertEquals('0 B', $result);
    }

    public function test_sanitize_float()
    {
        $result = sanitizeFloat('1,234.56');
        $this->assertEquals(1234.56, $result);
    }

    public function test_sanitize_float_null()
    {
        $result = sanitizeFloat(null);
        $this->assertEquals('0.0', $result);
    }

    public function test_supprimer_decoration()
    {
        $result = supprimer_decoration('1,234.56');
        $this->assertEquals(1234.56, $result);
    }

    public function test_supprimer_decoration_null()
    {
        $result = supprimer_decoration(null);
        $this->assertEquals('0', $result);
    }

    public function test_supprimer_decoration_no_separator()
    {
        $input = '1,234,567';
        $result = supprimer_decoration($input);
        $this->assertEquals(1234.567, $result);
    }

    public function test_bool_val()
    {
        $this->assertTrue(bool_val('true'));
        $this->assertFalse(bool_val('false'));
    }
}
