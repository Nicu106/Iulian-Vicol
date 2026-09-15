<?php

return [

    /* How long a link keeps counting after someone opens it. */
    'days' => 90,

    /* What the person who recommends gets, in the words /recomienda will use,
       e.g. "Te regalo 150 €" or "Te regalo un año más de garantía".
       null (the default) means the page promises nothing specific: the reward
       has not been decided, and a public page must not invent one. */
    'reward' => env('REFERRAL_REWARD'),

];
