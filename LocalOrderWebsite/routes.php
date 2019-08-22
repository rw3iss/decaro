<?php

//Set a default catch for non-implemented routes
$dorm_routes['default_route'] = 'defaultcontroller/pagenotfound';


//Home page
$dorm_routes['/'] = 'defaultcontroller/index';

//entry portal for logged-in users
$dorm_routes['/manage'] = 'defaultcontroller/manage';
$dorm_routes['/login'] = 'defaultcontroller/login';

/*
//see listing of all orders
$dorm_routes['/orders'] = 'ordercontroller/orders';

//create a new order
$dorm_routes['/editorder/new'] = 'ordercontroller/neworder';

//edit an existing order
$dorm_routes['/editorder/(:num)'] = 'ordercontroller/editorder/$1';
*/

//Partial templates/pages
$dorm_routes['/partial/(:any)'] = 'defaultcontroller/partial/$1';


$dorm_routes['/testgoogle'] = 'defaultcontroller/testgoogle';

//API routes
$dorm_routes['/service/restoreData'] = 'backupcontroller/restoreData';

$dorm_routes['/service/loginUser'] = 'usercontroller/loginUser';
$dorm_routes['/service/logoutUser'] = 'usercontroller/logoutUser';
$dorm_routes['/service/getCurrentUser'] = 'usercontroller/getCurrentUser';

$dorm_routes['/service/getAllOrders'] = 'ordercontroller/getAllOrders';
$dorm_routes['/service/getOrder/(:num)'] = 'ordercontroller/getOrder/$1';
$dorm_routes['/service/startNewOrder'] = 'ordercontroller/startNewOrder';
$dorm_routes['/service/saveOrder/(:num)'] = 'ordercontroller/saveOrder/$1';
$dorm_routes['/service/removeOrder/(:num)'] = 'ordercontroller/removeOrder/$1';
$dorm_routes['/service/viewOrderPDF/(:num)'] = 'ordercontroller/viewOrderPDF/$1';
$dorm_routes['/service/generateOrderPDF/(:num)'] = 'ordercontroller/generateOrderPDF/$1';

$dorm_routes['/service/getAllClients'] = 'clientcontroller/getAllClients';
$dorm_routes['/service/getClient/(:num)'] = 'clientcontroller/getClient/$1';
$dorm_routes['/service/startNewClient'] = 'clientcontroller/startNewClient';
$dorm_routes['/service/saveClient/(:num)'] = 'clientcontroller/saveClient/$1';
$dorm_routes['/service/removeClient/(:num)'] = 'clientcontroller/removeClient/$1';

$dorm_routes['/service/getStationsForClient/(:num)'] = 'clientcontroller/getStationsForClient/$1';
$dorm_routes['/service/saveClientStation/(:num)'] = 'clientcontroller/saveClientStation/$1';
$dorm_routes['/service/removeClientStation/(:num)'] = 'clientcontroller/removeClientStation/$1';

$dorm_routes['/service/getInvoices'] = 'invoicecontroller/getInvoices'; // DEFUNCT
$dorm_routes['/service/getAllInvoices'] = 'invoicecontroller/getAllInvoices';
$dorm_routes['/service/getInvoice/(:num)'] = 'invoicecontroller/getInvoice/$1';
$dorm_routes['/service/saveInvoice/(:num)'] = 'invoicecontroller/saveInvoice/$1';
$dorm_routes['/service/removeInvoice/(:num)'] = 'invoicecontroller/removeInvoice/$1';
$dorm_routes['/service/viewInvoicePDF/(:num)'] = 'invoicecontroller/viewInvoicePDF/$1';
$dorm_routes['/service/viewManifestPDF/(:num)'] = 'invoicecontroller/viewManifestPDF/$1';
$dorm_routes['/service/generateInvoicePDF/(:num)'] = 'invoicecontroller/generateInvoicePDF/$1';
$dorm_routes['/service/generateManifestPDF/(:num)'] = 'invoicecontroller/generateManifestPDF/$1';

$dorm_routes['/service/getAllUsers'] = 'usercontroller/getAllUsers';
$dorm_routes['/service/getUser/(:num)'] = 'usercontroller/getUser/$1';
$dorm_routes['/service/startNewUser'] = 'usercontroller/startNewUser';
$dorm_routes['/service/saveUser/(:num)'] = 'usercontroller/saveUser/$1';
$dorm_routes['/service/removeUser/(:num)'] = 'usercontroller/removeUser/$1';
$dorm_routes['/service/getAllUserRoles'] = 'usercontroller/getAllUserRoles';

$dorm_routes['/service/getAllSettings'] = 'settingscontroller/getAllSettings';
$dorm_routes['/service/getSetting/(:num)'] = 'settingscontroller/getSetting/$1';
$dorm_routes['/service/saveSetting/(:num)'] = 'settingscontroller/saveSetting/$1';
$dorm_routes['/service/removeSetting/(:num)'] = 'settingscontroller/removeSetting/$1';

$dorm_routes['/service/getOrdersForClient/(:num)'] = 'ordercontroller/getOrdersForClient/$1';
$dorm_routes['/service/getOrdersForClientStation/(:num)'] = 'ordercontroller/getOrdersForClientStation/$1';
$dorm_routes['/service/getPaymentsForClient/(:num)'] = 'paymentcontroller/getPaymentsForClient/$1';

// 404 page not found route
$dorm_routes['404'] = 'defaultcontroller/page_not_found';

?>