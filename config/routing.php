<?php

Routes::get('/', function () {
    return [];
});



Routes::group(array('prefix' => '/query'), function () {

    Routes::any('/ip', 'controller\query\IpAction@outputIP');

});