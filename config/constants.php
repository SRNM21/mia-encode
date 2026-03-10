<?php

// Here, you can declare a constants or templates that you can use in the application.
// This helps to minimize declaring literal values and also makes the application's
// system easy to configure without going to each file.

return [
    
    'validator' => [

        // The prefix ':' indicates that the word is a placeholder for a true value
        'error_message' => [

            // Available prefix [ :attribute ]
            'required' => 'The :attribute field is required',
            
            // Available prefix [ :attribute ]
            'string' => 'The :attribute must be a string',
            
            // Available prefix [ :attribute ]
            'integer' => 'The :attribute must be an integer', 
            
            // Available prefix [ :attribute, :min ]
            'min_string' => 'The :attribute must be at least :min characters',
            'min_integers' => 'The :attribute must be at least :min', 
            
            // Available prefix [ :attribute, :max ]
            'max_string' => 'The :attribute must not exceed :max characters',
            'max_integers' => 'The :attribute must not exceed :max', 
            
            // Available prefix [ :attribute ]
            'in' => 'The selected :attribute is not in the list of allowed values', 
        ]
    ], 

];