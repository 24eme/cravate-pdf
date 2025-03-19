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

        $outputFile = [
            'pdf' => $outputDir.basename($pdfPath, '.pdf').'_filled.pdf',
            'xfdf' => $xfdfFilename
        ];

        $xfdfFile = fopen($xfdfFilename, "w");
        fwrite($xfdfFile, self::generateXFDF($pdfPath, $data));

        $proc = proc_open([self::command, $pdfPath, 'fill_form', $xfdfFilename, 'output', $outputFile['pdf'], 'flatten'], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ], $pipes);

        if ($proc === false) {
            throw new \Exception('Execution failed : '.stream_get_contents($pipes[2]));
        }

        fclose($pipes[1]);
        proc_close($proc);
        fclose($xfdfFile);

        return $outputFile;
    }
}
