<?php
declare(strict_types=1);

// Dostosowanie eksportu CSV dostawcy: nazwy kolumn i wartości zewnętrzne.
return [
    'columns' => [
        'external_id' => 'Ticket ID',
        'title' => 'Subject',
        'requester' => 'Requester',
        'category' => 'Type',
        'priority' => 'Severity',
        'status' => 'State',
        'description' => 'Details',
    ],
    'category' => [
        'Device' => 'hardware',
        'Application' => 'software',
        'Permissions' => 'access',
        'Other' => 'other',
    ],
    'priority' => [
        'P1' => 'high',
        'P2' => 'normal',
        'P3' => 'low',
    ],
    'status' => [
        'Open' => 'new',
        'Assigned' => 'in_progress',
        'Resolved' => 'done',
    ],
];

