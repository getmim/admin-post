<?php 

return [
    'LibUserPerm\\Model\\UserPerm' => [
        'data' => [
            'name' => [
                'manage_post'      => ['group'=>'Post','about'=>'Allow user to manage own posts'],
                'manage_post_all'  => ['group'=>'Post','about'=>'Allow user to manage all posts'],
                'remove_post'      => ['group'=>'Post','about'=>'Allow user to remove posts'],
                'publish_post'     => ['group'=>'Post','about'=>'Allow user to publish posts'],
                'feature_post'     => ['group'=>'Post','about'=>'Allow user to set featured posts'],
                'editor_pick_post' => ['group'=>'Post','about'=>'Allow user to set editor pick posts']
            ]
        ]
    ]
];