<?php

return [
    'profanity' => array_filter(array_map('trim', explode(',', (string) env('MODERATION_PROFANITY', 'damn,hell,crap,bastard,bloody')))),

    'restricted' => array_filter(array_map('trim', explode(',', (string) env('MODERATION_RESTRICTED', 'confidential,classified,terrorism,weaponized')))),
];
