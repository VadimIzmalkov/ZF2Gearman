<?php 
namespace Zf2Gearman;

return [
	'console' => [
		'router' => [
			'routes' => [
				// Cron job example: 
                // */50 * * * * php /var/www/html/fryday/public/index.php add-job <jobtype> <ip> 0 0

                // run CLI command to start separate process
                'add-job' => [
                    'options' => [
                        'route' => 'add-job <jobid> <createownworker>',
                        'defaults' => [
                            '__NAMESPACE__' => 'Zf2Gearman\Controller',
                            'controller'    => 'index',
                            'action'        => 'add-job',
                        ],
                    ]
                ],

                // run worker in separate process
                'add-background-job-console' => [
                    'options' => [
                        'route' => 'add-background-job-console <jobid> <createownworker>',
                        'defaults' => [
                            '__NAMESPACE__' => 'Zf2Gearman\Controller',
                            'controller'    => 'index',
                            'action'        => 'add-background-job-console',
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