<?php

return [
    '__name' => 'admin-post',
    '__version' => '0.0.5',
    '__git' => 'git@github.com:getmim/admin-post.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/admin-post' => ['install','update','remove'],
        'theme/admin/post' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'admin' => NULL
            ],
            [
                'post' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'lib-pagination' => NULL
            ],
            [
                'lib-upload' => NULL
            ],
            [
                'admin-site-meta' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'AdminPost\\Controller' => [
                'type' => 'file',
                'base' => 'modules/admin-post/controller'
            ],
            'AdminPost\\Library' => [
                'type' => 'file',
                'base' => 'modules/admin-post/library'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'admin' => [
            'adminPost' => [
                'path' => [
                    'value' => '/post'
                ],
                'method' => 'GET',
                'handler' => 'AdminPost\\Controller\\Post::index'
            ],
            'adminPostEdit' => [
                'path' => [
                    'value' => '/post/(:id)',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminPost\\Controller\\Post::edit'
            ],
            'adminPostRemove' => [
                'path' => [
                    'value' => '/post/(:id)/remove',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminPost\\Controller\\Post::remove'
            ]
        ]
    ],
    'adminUi' => [
        'sidebarMenu' => [
            'items' => [
                'post' => [
                    'label' => 'Post',
                    'icon' => '<i class="fas fa-newspaper"></i>',
                    'priority' => 0,
                    'children' => [
                        'all-post' => [
                            'label' => 'All Post',
                            'icon'  => '<i></i>',
                            'route' => ['adminPost'],
                            'perms' => 'manage_post'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'admin.post.edit' => [
                '@extends' => ['std-site-meta','std-cover'],
                'title' => [
                    'label' => 'Title',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ],
                'slug' => [
                    'label' => 'Slug',
                    'type' => 'text',
                    'slugof' => 'title',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE,
                        'unique' => [
                            'model' => 'Post\\Model\\Post',
                            'field' => 'slug',
                            'self' => [
                                'service' => 'req.param.id',
                                'field' => 'id'
                            ]
                        ]
                    ]
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'select',
                    'rules' => [
                        'required' => true 
                    ]
                ],
                'embed' => [
                    'label' => 'Embed',
                    'type' => 'textarea',
                    'rules' => []
                ],
                'featured' => [
                    'label' => 'Featured',
                    'type' => 'checkbox',
                    'rules' => [],
                    'filters' => [
                        'boolean' => true 
                    ]
                ],
                'editor_pick' => [
                    'label' => 'Editor Pick',
                    'type' => 'checkbox',
                    'rules' => [],
                    'filters' => [
                        'boolean' => true 
                    ]
                ],
                'content' => [
                    'label' => 'About',
                    'type' => 'summernote',
                    'rules' => [
                        'required' => true
                    ]
                ],
                'meta-schema' => [
                    'options' => [
                        'Article'       => 'Article',
                        'BlogPosting'   => 'BlogPosting',
                        'CreativeWork'  => 'CreativeWork',
                        'NewsArticle'   => 'NewsArticle',
                        'Report'        => 'Report',
                        'Review'        => 'Review',
                        'TechArticle'   => 'TechArticle'
                    ]
                ]
            ],
            'admin.post.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => TRUE,
                    'rules' => []
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'select',
                    'nolabel' => TRUE,
                    'options' => [
                        '0' => 'All',
                        '1' => 'Draft',
                        '2' => 'Editor',
                        '3' => 'Published'
                    ],
                    'rules' => []
                ]
            ]
        ]
    ],
    'admin' => [
        'objectFilter' => [
            'handlers' => [
                'post' => 'AdminPost\\Library\\Filter'
            ]
        ]
    ]
];
