<?php

$routes = [

    # System controller
    '' => [
        'controller'=> 'main',
        'function'  => 'index',
        'initializers' => [
            'global',
        ]
    ],
    'page-not-found' => [
        'controller'=> 'system',
        'function'  => 'page_not_found',
        'initializers' => [
            'global',
        ]
    ],
    'maintenance' => [
        'controller'=> 'system',
        'function'  => 'maintenance',
        'initializers' => [
            'global',
        ]
    ],
    'logout' => [
        'controller'=> 'system',
        'function'  => 'logout',
        'initializers' => [
            'global',
        ]
    ],

    # Asana Communication
    'asana/parameters' => [
        'controller'=> 'asana_api',
        'function'  => 'parameters',
        'initializers' => [
            'global', 'session', 'csrf',
        ]
    ],
    'asana/authorize' => [
        'controller'=> 'asana_api',
        'function'  => 'authorize',
        'initializers' => [
            'global'
        ],
    ],
    'asana/callback' => [
        'controller'=> 'asana_api',
        'function'  => 'callback',
        'initializers' => [
            'global'
        ],
    ],

    # Dashboard controller
    'dashboard' => [
        'controller'=> 'dashboard/graph',
        'function'  => 'graph',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ],
    ],
    'dashboard/graph' => [
        'controller'=> 'dashboard/graph',
        'function'  => 'graph',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/reports' => [
        'controller'=> 'dashboard/reports',
        'function'  => 'reports',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/search' => [
        'controller'=> 'dashboard/search',
        'function'  => 'search',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/collaborators/groups' => [
        'controller'=> 'dashboard/collaborators',
        'function'  => 'collaborators',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/real-time' => [
        'controller'=> 'dashboard/real_time',
        'function'  => 'real_time',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/account' => [
        'controller'=> 'dashboard/account',
        'function'  => 'account',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/history' => [
        'controller'=> 'dashboard/history',
        'function'  => 'history',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/support' => [
        'controller'=> 'dashboard/support',
        'function'  => 'support',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ]
    ],
    'dashboard/settings' => [
        'controller'=> 'dashboard/settings/settings',
        'function'  => 'settings_index',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ],
    ],
    'dashboard/settings/db-sync' => [
        'controller'=> 'dashboard/settings/db_sync',
        'function'  => 'db_sync',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ],
    ],
    'dashboard/settings/db-sync/direct' => [
        'controller'=> 'dashboard/settings/db_sync_direct_import',
        'function'  => 'db_sync_direct_import',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ],
    ],
    'dashboard/settings/db-sync/prefill' => [
        'controller'=> 'dashboard/settings/db_sync_prefill',
        'function'  => 'asana_api_structure_scanner',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ],
    ],
    'dashboard/settings/webhooks' => [
        'controller'=> 'dashboard/settings/web_hooks',
        'function'  => 'main_screen',
        'initializers' => [
            'global', 'session', 'token', 'maintenance',
        ],
    ],
    'dashboard/settings/webhooks/asana-ping' => [
        'controller'=> 'dashboard/settings/web_hooks',
        'function'  => 'asana_ping',
        'initializers' => [
            'global'
        ],
    ],
];