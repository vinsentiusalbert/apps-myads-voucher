<?php

return [
    'api_base_url' => env('MYADS_API_BASE_URL', 'http://localhost:3000'),
    // Optional: pre-uploaded/encrypted MMS file token for dummy Targeted creation.
    'dummy_mms_file' => env('MYADS_DUMMY_MMS_FILE', ''),
    // Optional defaults for WABA template creation. If empty, the app will try display-name/list.
    'waba_account_id' => env('MYADS_WABA_ACCOUNT_ID', ''),
    'waba_phone_number_id' => env('MYADS_WABA_PHONE_NUMBER_ID', ''),
];
