<?php

return [
    'api_base_url' => env('MYADS_API_BASE_URL', 'http://localhost:3000'),
    // Optional: pre-uploaded/encrypted MMS file token for dummy Targeted creation.
    'dummy_mms_file' => env('MYADS_DUMMY_MMS_FILE', ''),
];
