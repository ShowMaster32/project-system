<?php

return [
    // Comma-separated list of global admin emails, e.g. in .env: PMT_GLOBAL_ADMINS="admin@example.com,owner@example.com"
    'global_admin_emails' => array_filter(array_map('trim', explode(',', env('PMT_GLOBAL_ADMINS', '')))),
];
