<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', 'MemberServiceController@api_user_middleware');


Route::get('services/{id_service_parent}', 'ServiceListController@show_as_json')->name('services_api.show');
Route::post('services/{id_service_parent}/add', 'ServiceListController@add_child')->name('services_api.create');
Route::post('services/{id_service_parent}/update', 'ServiceListController@update_child')->name('services_api.update');
Route::get('services/{id_service_parent}/destroy', 'ServiceListController@delete_child')->name('services_api.delete');

Route::group(['prefix'=>'data'], function(){
  Route::any("agency_units", "AgencyUnitController@list")->name('api-list-agency-units');
  Route::any("service_units", "ServiceUnitController@list")->name('api-list-service-units');
  Route::any("service_list", "ServiceListController@list")->name('api-list-service-list');
  Route::any("service_list_search_by", "ServiceListController@search_by")->name('api-list-service-list-search-by');
  Route::get("agency_unit_search_by", "AgencyUnitController@search_by")->name('api-list-agency-units-search-by');
  Route::any("countries", "CountryController@list")->name('api-list-countries');
  Route::any("currencies", "CurrencyController@list")->name('api-list-currencies');
  Route::any("holidays", "HolidayController@list")->name('api-list-holidays');
  Route::any("coas", "CoaController@list")->name('api-list-coas');
  Route::any("pricelist", "PriceListController@list")->name('api-list-pricelist');
  Route::any("users", "UserController@list")->name('api-list-users');
});

Route::group(['prefix'=>'summary'], function(){
  Route::get('ontime_and_delay/{id_agency_unit}', 'ApiFrontEndController@summary_ontime_and_delay')->name('api-summary-ontime');
  Route::get('ongoing/{id_agency_unit}', 'ApiFrontEndController@summary_ongoing')->name('api-summary-ongoing');
});
