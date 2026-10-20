<?php

return [
    // Dummy access codes. Replace with unique buyer vouchers before production.
    'MYADS20K' => ['price' => 20000, 'report_days' => 7, 'sms_quota' => 400, 'campaign_type' => 'sms_lba'],
    'MYADS25K' => ['price' => 25000, 'report_days' => 7, 'sms_quota' => 800, 'campaign_type' => 'sms_lba'],
    'MYADS50K' => ['price' => 50000, 'report_days' => 30, 'sms_quota' => 10000, 'campaign_type' => 'sms_lba'],
    'RPT20K01' => ['price' => 20000, 'report_days' => 7, 'sms_quota' => 400, 'campaign_type' => 'sms_lba', 'report_demo' => true],
];
