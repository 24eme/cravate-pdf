<?php

namespace Records;

class Records
{
    const FOLDER = __DIR__.'/../../records/';

    public static function getRecords() {
        $records = scandir(self::FOLDER);
        if (!$records) {
            return [];
        }
        $items = [];
        foreach($records as $record) {
            if (in_array($record, ['.', '..'])) {
                continue;
            }
            $items[] = new Record($record);
        }
        return $items;
    }
}
