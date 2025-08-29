<?php

    $config = array(
        'healththemes' =>array(
                [
                    'field'=>'description', 
                    'label'=>'Theme Description',
                    'rules'=>'required|min_length[4]|max_length[100]|is_unique[thematic_area.description]',
                    'errors' => [
                        'required'     => 'You have not provided %s.',
                        'is_unique'    => 'This %s already exists.',
                        'max_length'   => '%s cannot be longer than 50 characters.'
                    ]
                ]
            ),
        'author'=>array(
                [
                    'field'=>'name', 
                    'label'=>'Author Name',
                    'rules'=>'required|min_length[4]|max_length[100]',
                    'errors' => [
                        'required'     => 'You have not provided %s.',
                        'is_unique'    => 'This %s already exists.'
                    ]
                ]
            ),
    );