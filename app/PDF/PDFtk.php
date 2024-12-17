<?php

namespace PDF;

class PDFtk
{
    const command = 'pdftk';

    public static function dumpDataFields($pdfFile) {
        $proc = proc_open([self::command, $pdfFile, 'dump_data_fields_utf8'], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ], $pipes);

        if ($proc === false) {
            throw new \Exception('Execution failed : '.stream_get_contents($pipes[2]));
        }

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($proc);

        return $output;
    }

    public static function parseDataFieldsDump($dump) {
        $dataFields = [];
        $i = -1;
        foreach(explode("\n", $dump) as $line) {
            if($line === '---') {
                $i++;
                continue;
            }
            if(strpos($line, ': ') === false)  {
                continue;
            }
            $key = explode(": ", $line)[0];
            $value = explode(": ", $line)[1];

            if(isset($dataFields[$i][$key]) && !is_array($dataFields[$i][$key])) {
                $dataFields[$i][$key] = [$dataFields[$i][$key]];
            }
            if(isset($dataFields[$i][$key]) && is_array($dataFields[$i][$key])) {
                $dataFields[$i][$key][] = $value;
            } else {
                $dataFields[$i][$key] = $value;
            }
        }
        return $dataFields;
    }

    public static function cleanData(array $parsedData, array $sentData)
    {
        $cleaned = [];

        foreach ($sentData as $fieldName => $value) {
            if (in_array($fieldName, array_keys($parsedData)) === false) {
                continue;
            }

            if ($parsedData[$fieldName]->getType() === PDFFormField::TYPE_CHECKBOX) {
                $value = $value ? current($parsedData[$fieldName]->getChoices()) : "Off";
            } elseif ($parsedData[$fieldName]->getType() === PDFFormField::TYPE_RADIO) {
                $value = in_array($value, $parsedData[$fieldName]->getChoices()) ? $value : "Off";
            } elseif ($parsedData[$fieldName]->getType() === PDFFormField::TYPE_SELECT) {
                $value = in_array($value, $parsedData[$fieldName]->getChoices()) ? $value : null;
            }

            $cleaned[$fieldName] = $value;
        }

        return $cleaned;
    }

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

        $outputFile = $outputDir.basename($pdfPath, '.pdf').'_filled.pdf';

        $xfdfFilename = tempnam($outputDir, "XFDF");
        $xfdfFile = fopen($xfdfFilename, "w");
        fwrite($xfdfFile, self::generateXFDF($pdfPath, $data));

        $proc = proc_open([self::command, $pdfPath, 'fill_form', $xfdfFilename, 'output', $outputFile, 'flatten'], [
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
