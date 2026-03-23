<?php

namespace PDF;

class PDFtk
{
    const command = 'pdftk';

    public static function generateXFDF(string $pdfPath, array $data)
    {
        return sprintf('<?xml version="1.0" encoding="UTF-8"?>
        <xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">
          <f href="%s"/>
          <fields>
            %s
          </fields>
        </xfdf>', $pdfPath, self::buildXFDFFields($data));
    }

    private static function buildXFDFFields(array $data)
    {
        $fields = [];

        foreach ($data as $fieldName => $value) {
            $fields[] = "<field name=\"$fieldName\"><value>$value</value></field>".PHP_EOL;
        }

        return implode('', $fields);
    }

    public static function fillForm(string $pdfPath, array $data, string $outputDir = '/tmp/')
    {
        if (empty($data)) {
            throw new \Exception('Il faut spécifier des données');
        }

        if (! $pdfPath || is_file($pdfPath) === false) {
            throw new \Exception('Il faut spécifier un fichier PDF');
        }

        $xfdfFilename = tempnam($outputDir, "XFDF");

        $tmpPdf = $outputDir.basename($pdfPath, '.pdf').'_tmp.pdf';

        $outputFile = [
            'pdf' => $outputDir.basename($pdfPath, '.pdf').'_filled.pdf',
            'xfdf' => $xfdfFilename
        ];

        $xfdfFile = fopen($xfdfFilename, "w");
        fwrite($xfdfFile, self::generateXFDF($pdfPath, $data));
        fclose($xfdfFile);

        $proc = proc_open(
            [self::command, $pdfPath, 'fill_form', $xfdfFilename, 'output', $tmpPdf],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes
        );

        if ($proc === false) {
            throw new \Exception('pdftk execution failed');
        }

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        if ($error) {
            throw new \Exception('pdftk error: '.$error);
        }

        $proc = proc_open(
            [
                'gs',
                '-o', $outputFile['pdf'],
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-dNOPAUSE',
                '-dQUIET',
                '-dBATCH',
                $tmpPdf
            ],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes
        );

        if ($proc === false) {
            throw new \Exception('Gs execution failed');
        }

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        if ($error) {
            throw new \Exception('Gs error: '.$error);
        }

        unlink($tmpPdf);

        return $outputFile;
    }
}
