<?php 
namespace Zf2Gearman;

return [
	'console' => [
		'router' => [
			'routes' => [
				// Cron job example: 
                // */50 * * * * php /var/www/html/fryday/public/index.php add-job <jobtype> <ip> 0 0

                // run CLI command to start separate process
                'add-task' => [
                    'options' => [
                        'route' => 'add-task <workloadid> <force>',
                        'defaults' => [
                            '__NAMESPACE__' => 'Zf2Gearman\Controller',
                            'controller'    => 'gearman',
                            'action'        => 'add-job',
                        ],
                    ]
                ],

                'run-worker-console' => [
                    'options' => [
                        'route' => 'run-worker-console <workloadid> <force>',
                        'defaults' => [
                            '__NAMESPACE__' => 'Zf2Gearman\Controller',
                            'controller'    => 'gearman',
                            'action'        => 'run-worker-console',
                        ],
                    ],
                ],
			],
		],
	],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/' . __NAMESPACE__ . '/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                ],
            ],
        ],
    ],
];