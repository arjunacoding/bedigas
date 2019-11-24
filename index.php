<?php
require 'bedigas/Bedigas.php';

Bedigas::route('/', function(){
    echo 'hello world!';
});

Bedigas::start();
