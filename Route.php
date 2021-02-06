<?php
/**
 * API PARA AUTHENTICATION
 */
Request::POST('/auth', 'Usuarios@auth');
Request::POST('/auth/refresh', 'Usuarios@refresh');


/**
 * API PARA SÓCIOS
 */
Request::GET('/usuarios', 'Usuarios@index', true);
Request::POST('/usuario', 'Usuarios@store', true);
Request::POST('/usuario/{cpf}', 'Usuarios@update', true);
