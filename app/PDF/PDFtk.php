<?php

namespace PDF;

class PDFtk
{
    const command = 'pdftk';

    public static function dumpDataFields($pdfFile) {
        $proc = proc_open([self::command, $pdfFile, 'dump_data_fields'], [
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
}
