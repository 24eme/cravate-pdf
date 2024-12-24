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
            try {
                $items[] = new Record($record);
            } catch (\Exception $e) {
                continue;
            }
        }
        return $items;
    }
}
