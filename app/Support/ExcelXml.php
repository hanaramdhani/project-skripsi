<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penulis file Excel sederhana tanpa dependency (format SpreadsheetML 2003 / XML).
 * Didukung native oleh Microsoft Excel & LibreOffice, mendukung banyak worksheet,
 * format angka, merge cell, lebar kolom otomatis, dan wrap text. Dipakai untuk
 * export laporan keuangan.
 *
 * Cara pakai:
 *   $xls = new ExcelXml();
 *   $xls->addSheet('Jurnal Umum', $rows);
 *   return $xls->download('laporan.xls');
 *
 * Setiap $rows adalah array of baris. Baris = array of cell (lihat text()/number()),
 * atau ExcelXml::row([...], $tinggi) bila ingin mengatur tinggi baris.
 */
class ExcelXml
{
    /** @var array<int, array{name:string, rows:array}> */
    private $sheets = [];

    /** Style yang TIDAK dihitung saat menentukan lebar kolom otomatis. */
    private static $excludeFromWidth = ['header', 'title', 'subtitle'];

    public function addSheet($name, array $rows)
    {
        $this->sheets[] = ['name' => $this->sanitizeSheetName($name), 'rows' => $rows];
        return $this;
    }

    // ---- Helper pembuat cell ----------------------------------------------

    public static function text($value, $style = null, $mergeAcross = 0)
    {
        return ['v' => $value, 't' => 'String', 's' => $style, 'm' => $mergeAcross];
    }

    public static function number($value, $style = 'money', $mergeAcross = 0)
    {
        return ['v' => (float) $value, 't' => 'Number', 's' => $style, 'm' => $mergeAcross];
    }

    public static function blank($style = null, $mergeAcross = 0)
    {
        return ['v' => '', 't' => 'String', 's' => $style, 'm' => $mergeAcross];
    }

    /** Bungkus baris dengan tinggi khusus (mis. untuk header yang di-wrap). */
    public static function row(array $cells, $height = null)
    {
        return ['cells' => $cells, 'height' => $height];
    }

    // ---- Output ------------------------------------------------------------

    public function download($filename)
    {
        $xml = $this->render();

        $response = new StreamedResponse(function () use ($xml) {
            echo $xml;
        });

        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="' . str_replace('"', '', $filename) . '"'
        );
        $response->headers->set('Cache-Control', 'max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'public');

        return $response;
    }

    public function render()
    {
        $out  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $out .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
              . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
              . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
              . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
              . ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        $out .= $this->stylesXml();

        foreach ($this->sheets as $sheet) {
            $out .= '<Worksheet ss:Name="' . $this->attr($sheet['name']) . '">' . "\n";
            $out .= '<Table>' . "\n";

            foreach ($this->computeColumns($sheet['rows']) as $i => $w) {
                $out .= '<Column ss:Index="' . $i . '" ss:AutoFitWidth="0" ss:Width="' . $w . '"/>' . "\n";
            }

            foreach ($sheet['rows'] as $row) {
                $out .= $this->rowXml($row);
            }
            $out .= '</Table>' . "\n";
            $out .= '</Worksheet>' . "\n";
        }

        $out .= '</Workbook>';

        return $out;
    }

    // ---- Internal ----------------------------------------------------------

    private function cellsOf($row)
    {
        return isset($row['cells']) ? $row['cells'] : $row;
    }

    private function heightOf($row)
    {
        return isset($row['height']) ? $row['height'] : null;
    }

    /** Lebar kolom (poin) berdasarkan panjang string data terpanjang (bukan header). */
    private function computeColumns($rows)
    {
        $chars  = [];
        $maxCol = 0;

        foreach ($rows as $row) {
            $col = 1;
            foreach ($this->cellsOf($row) as $cell) {
                $merge = isset($cell['m']) ? (int) $cell['m'] : 0;
                $style = isset($cell['s']) ? $cell['s'] : null;

                // Hanya cell tunggal (tidak di-merge) & bukan style yang dikecualikan
                // (header/title/subtitle) yang menentukan lebar kolom.
                if ($merge === 0 && !in_array($style, self::$excludeFromWidth, true)) {
                    $len = $this->displayLen($cell);
                    if (!isset($chars[$col]) || $len > $chars[$col]) {
                        $chars[$col] = $len;
                    }
                }
                $col += $merge + 1;
            }
            if ($col - 1 > $maxCol) {
                $maxCol = $col - 1;
            }
        }

        $cols = [];
        for ($i = 1; $i <= $maxCol; $i++) {
            $n = isset($chars[$i]) ? $chars[$i] : 8;
            $w = $n * 6.2 + 14;              // perkiraan lebar (poin) untuk Calibri 11
            if ($w < 34)  $w = 34;
            if ($w > 340) $w = 340;
            $cols[$i] = round($w, 1);
        }
        return $cols;
    }

    private function displayLen($cell)
    {
        if ($cell['t'] === 'Number') {
            return strlen(number_format((float) $cell['v'], 2, ',', '.'));
        }
        $v = (string) $cell['v'];
        return function_exists('mb_strlen') ? mb_strlen($v) : strlen($v);
    }

    private function rowXml($row)
    {
        $cells  = $this->cellsOf($row);
        $height = $this->heightOf($row);
        $attr   = $height ? ' ss:Height="' . (float) $height . '" ss:AutoFitHeight="0"' : '';

        $xml = '<Row' . $attr . '>';
        foreach ($cells as $cell) {
            $style = isset($cell['s']) && $cell['s'] ? ' ss:StyleID="' . $this->attr($cell['s']) . '"' : '';
            $merge = isset($cell['m']) && $cell['m'] > 0 ? ' ss:MergeAcross="' . (int) $cell['m'] . '"' : '';
            $type  = $cell['t'] === 'Number' ? 'Number' : 'String';
            $value = $cell['t'] === 'Number' ? $this->numval($cell['v']) : $this->text_($cell['v']);
            $xml  .= '<Cell' . $style . $merge . '><Data ss:Type="' . $type . '">' . $value . '</Data></Cell>';
        }
        $xml .= '</Row>' . "\n";
        return $xml;
    }

    private function stylesXml()
    {
        return <<<XML
<Styles>
 <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Bottom"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>
 <Style ss:ID="title"><Font ss:Bold="1" ss:Size="14"/><Alignment ss:Vertical="Center" ss:WrapText="1"/></Style>
 <Style ss:ID="subtitle"><Font ss:Size="11" ss:Color="#555555"/><Alignment ss:Vertical="Center" ss:WrapText="1"/></Style>
 <Style ss:ID="header"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#343A40" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>
 <Style ss:ID="group"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#6C757D" ss:Pattern="Solid"/><Alignment ss:Vertical="Center" ss:WrapText="1"/></Style>
 <Style ss:ID="bold"><Font ss:Bold="1"/></Style>
 <Style ss:ID="cell"><Alignment ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/></Borders></Style>
 <Style ss:ID="cellCenter"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/></Borders></Style>
 <Style ss:ID="money"><NumberFormat ss:Format="#,##0.00"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDDDDD"/></Borders></Style>
 <Style ss:ID="moneyBold"><Font ss:Bold="1"/><NumberFormat ss:Format="#,##0.00"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Interior ss:Color="#F2F2F2" ss:Pattern="Solid"/></Style>
 <Style ss:ID="moneyTotal"><Font ss:Bold="1" ss:Color="#FFFFFF"/><NumberFormat ss:Format="#,##0.00"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Interior ss:Color="#343A40" ss:Pattern="Solid"/></Style>
 <Style ss:ID="totalLabel"><Font ss:Bold="1"/><Alignment ss:Horizontal="Right" ss:Vertical="Center" ss:WrapText="1"/><Interior ss:Color="#F2F2F2" ss:Pattern="Solid"/></Style>
 <Style ss:ID="totalLabelDark"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Alignment ss:Horizontal="Right" ss:Vertical="Center" ss:WrapText="1"/><Interior ss:Color="#343A40" ss:Pattern="Solid"/></Style>
</Styles>

XML;
    }

    private function sanitizeSheetName($name)
    {
        $name = str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $name);
        $name = trim($name);
        if (function_exists('mb_substr')) {
            return mb_substr($name, 0, 31);
        }
        return substr($name, 0, 31);
    }

    private function attr($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function text_($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function numval($v)
    {
        return rtrim(rtrim(number_format((float) $v, 4, '.', ''), '0'), '.') ?: '0';
    }
}
