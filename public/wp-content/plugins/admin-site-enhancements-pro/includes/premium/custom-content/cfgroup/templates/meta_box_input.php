<?php

CFG()->form->load_assets();

echo CFG()->form( [
    'object_type'   => 'post',
    'post_id'       => $post->ID,
    'field_groups'  => $metabox['args']['group_id'],
    'front_end'     => false,
] );
