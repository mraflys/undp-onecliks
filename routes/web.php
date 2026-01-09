<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'LoginController@Login');
Route::get('login', 'LoginController@index')->name('login');
Route::post('login', 'LoginController@process')->name('auth.login');
Route::get('logout', 'LoginController@logout')->name('logout');
Route::get('register', 'LoginController@register')->name('register');
Route::post('register', 'LoginController@register_post')->name('auth.register');
Route::post('forgot_password', 'LoginController@forgot_password')->name('forgot_password');
Route::get('reset_password', 'LoginController@reset_password')->name('reset_password');

Route::group(['middleware' => 'auth:web'], function () {
    Route::get('login/test', 'LoginController@auth_test')->name('auth_page_test');
    # admin parts start from here
    Route::group(['prefix' => 'admin'], function () {
        Route::resource('agency_units', 'AgencyUnitController');
        Route::resource('service_units', 'ServiceUnitController');
        Route::resource('service_list', 'ServiceListController');

        Route::get('service_list/{id_service}/workflows', 'ServiceListController@show_workflow')->name('service_list.workflow');
        Route::get('service_list/{id_service}/workflows/add', 'WorkFlowController@create')->name('workflows.add');
        Route::get('service_list/{id_service}/delete_child', 'WorkFlowController@delete_child')->name('workflows.delete_child');
        Route::resource('workflows', 'WorkFlowController');
        Route::get('service_list/{id_service}/workflows/{id}/{arrow}', 'WorkFlowController@update_sequence')->name('workflows.sequence.update');

        Route::resource('pricelist', 'PriceListController');
        Route::get('service_list/{id_service}/pricelist/add', 'PriceListController@create')->name('pricelist.add');
        Route::get('service_list/{id_service}/pricelist', 'PriceListController@index')->name('member.pricelist.index');
        Route::resource('workflow_docs', 'WorkFlowDocController');
        Route::resource('workflow_infos', 'WorkFlowInfoController');
        Route::get('workflows/{id_service_workflow}/docs', 'WorkFlowDocController@index')->name('workflow.docs');
        Route::get('workflows/{id_service_workflow}/doc/add', 'WorkFlowDocController@create')->name('workflow_docs.add');
        Route::get('workflows/{id_service_workflow}/infos', 'WorkFlowInfoController@index')->name('workflow.infos');
        Route::get('workflows/{id_service_workflow}/info/add', 'WorkFlowInfoController@create')->name('workflow_infos.add');
        Route::get('workflows/{id_service_workflow}/info/{id}/{arrow}', 'WorkFlowInfoController@update_sequence')->name('workflow_infos.sequence.update');
        Route::get('workflows/{id_service_workflow}/doc/{id}/{arrow}', 'WorkFlowDocController@update_sequence')->name('workflow_docs.sequence.update');

        Route::post('project/person-list', 'ProjectController@person_list')->name('project.person-list');
        Route::post('project/get-inputname', 'ProjectController@get_inputname')->name('project.get-inputname');
        Route::post('project/setting-store', 'ProjectController@setting_store')->name('project.setting-store');
        Route::post('project/setting-store-input', 'ProjectController@setting_store_input')->name('project.setting-store-input');
        Route::get('project/ajax-list', 'ProjectController@ajax_list')->name('project.ajax-list');
        Route::get('project/excel', 'ProjectController@excel')->name('project.excel');

        Route::post('expenditur/expenditur-detail', 'ExpenditurController@expenditur_detail')->name('expenditur.expenditur-detail');
        Route::post('expenditur/update_new', 'ExpenditurController@update_new')->name('expenditur.update_new');
        Route::get('expenditur/ajax-list', 'ExpenditurController@ajax_list')->name('expenditur.ajax-list');

        Route::resource('countries', 'CountryController');
        Route::resource('currencies', 'CurrencyController');
        Route::resource('projects', 'ProjectController');
        Route::resource('expenditur', 'ExpenditurController');
        Route::resource('coas', 'CoaController');
        Route::resource('holidays', 'HolidayController');
        Route::resource('users', 'UserController');
        Route::resource('soft_deleted_users', 'SoftDeletedUserController');
        Route::get("soft_deleted_users/revive/{id}", 'SoftDeletedUserController@revive')->name("soft_deleted_users.revive");
        Route::resource('app_configs', 'AppConfigController');
    });

    Route::group(['prefix' => 'member-area'], function () {
        Route::group(['middleware' => 'auth:web'], function () {
            // if (\Session::get('user_id') == null) return redirect('login')->send();
            Route::get('test_code', 'MemberRequestController@test_code')->name('myrequests.test_code');
            Route::get('home', 'MemberRequestController@home')->name('myrequests.home');
            Route::get('requests', 'MemberRequestController@index')->name('myrequests.index');
            Route::get('requests/new', 'MemberRequestController@create')->name('myrequests.create');
            Route::post('requests/create', 'MemberRequestController@store')->name('myrequests.store');
            Route::post('requests/create/upload-file', 'MemberRequestController@upload_storage_file')->name('myrequests.store.upload.file');
            Route::get('requests/draft', 'MemberRequestController@draft')->name('myrequests.draft');
            Route::get('requests/draft/{id_draft}/edit', 'MemberRequestController@draft_edit')->name('myrequests.draft_edit');
            Route::post('requests/draft/{id_draft}/update', 'MemberRequestController@store')->name('myrequests.draft_update');
            Route::get('requests/draft/{id_draft}/delete', 'MemberRequestController@draft_delete')->name('myrequests.draft_delete');
            Route::get('requests/ongoing', 'MemberRequestController@ongoing')->name('myrequests.ongoing');
            Route::get('requests/history', 'MemberRequestController@history')->name('myrequests.history');
            Route::get('requests/tracking', 'MemberRequestController@tracking')->name('myrequests.tracking');
            Route::post('requests/tracking', 'MemberRequestController@tracking_request_search')->name('post.myrequests.tracking');
            Route::get('requests/{id_transaction}/edit', 'MemberRequestController@edit')->name('myrequests.edit');
            Route::post('requests/{id_transaction}/update', 'MemberRequestController@update')->name('myrequests.update');
            Route::get('requests/{id_transaction}/delete', 'MemberRequestController@delete_transaction')->name('myrequests.delete');

            Route::get('services', 'MemberServiceController@index')->name('myservices.index');
            Route::get('services/delete-doc/{id_transaction_workflow_doc}', 'MemberServiceController@delete_doc')->name('myservices.delete_doc');

            Route::get('services/delete-temporary-doc/{id_transaction_workflow_doc}', 'MemberServiceController@delete_temporary_doc')->name('myservices.delete_temporary_doc');

            Route::get('services/delete-temporary-doc-draft/{id_transaction_workflow_doc}', 'MemberServiceController@delete_temporary_doc_draft')->name('myservices.delete_temporary_doc_draft');
            Route::get('services/delete-temporary-doc-draft-coa-other/{id_workflow_doc_coa_other}', 'MemberServiceController@delete_temporary_doc_draft_coa_other')->name('myservices.delete_temporary_doc_draft_coa_other');

            Route::post('services/upload-final-doc', 'MemberServiceController@upload_final_doc')->name('myservices.upload_final_doc');

            Route::get('services/delete-info/{id_transaction_workflow_info}', 'MemberServiceController@delete_info')->name('myservices.delete_info');
            Route::get('services/{id_transaction}', 'MemberServiceController@show')->name('myservices.show');
            Route::get('services/{id_transaction}/view', 'MemberServiceController@view')->name('myservices.view');
            Route::get('services/download/{url}', 'MemberServiceController@download_file')->name('myservices.download_file');
            Route::get('services/{id_transaction}/response_action', 'MemberServiceController@view_with_response_action')
                ->name('myservices.view_with_response_action');
            Route::get('services/{id_transaction}/edit', 'MemberServiceController@edit')->name('myservices.edit');
            Route::get('services/{id_transaction}/assign_pic', 'MemberServiceController@assign_pic')->name('myservices.assign_pic');
            Route::post('services/reject', 'MemberServiceController@reject')->name('myservices.reject');
            Route::post('services/return', 'MemberServiceController@return')->name('myservices.return');
            Route::post('services/assign_pic', 'MemberServiceController@assign_pic')->name('post.myservices.assign_pic');
            Route::post('services/{id_transaction}/update_service', 'MemberServiceController@update_service')->name('myservices.update_service');
            Route::post('services/{id_transaction}/confirm_service', 'MemberServiceController@confirm_service')->name('myservices.confirm_service');
            Route::get('services/{id_transaction}/finish_service', 'MemberServiceController@finish_service')->name('myservices.finish_service');
            Route::post('services/rework_workflow', 'MemberServiceController@rework_workflow')->name('myservices.rework_workflow');
            Route::post('services/{id_transaction}/update_pic', 'MemberServiceController@update_pic')->name('myservices.update_pic');
            Route::get('services_tracking', 'MemberServiceController@tracking')->name('myservices.tracking');
            Route::post('services_rating', 'MemberServiceController@rate')->name('myservices.add_rating');

            Route::get('billing', 'MemberBillingController@index')->name('mybillings.index');
            Route::get('billing/add', 'MemberBillingController@add')->name('mybillings.add');
            Route::post('billing/add', 'MemberBillingController@create')->name('mybillings.create');
            Route::get('billing/{id}', 'MemberBillingController@show')->name('mybillings.show');
            Route::get('billing/{id}/group', 'MemberBillingController@show_group')->name('mybillings.show_group');
            Route::get('billing/{id}/group/finalize', 'MemberBillingController@finalize')->name('mybillings.finalize');
            Route::get('billing/{id}/group/edit', 'MemberBillingController@edit_group')->name('mybillings.edit_group');
            Route::post('billing/{id}/group/edit', 'MemberBillingController@update_group')->name('mybillings.update_group');
            Route::post('billing/delete', 'MemberBillingController@delete')->name('mybillings.delete');
            Route::get('billing/{id}/print', 'MemberBillingController@print')->name('mybillings.print');
            Route::post('billing/pay', 'MemberBillingController@pay')->name('mybillings.pay');
            Route::get('billing_glje', 'MemberBillingController@glje')->name('mybillings.glje_index');
            Route::get('billing_glje/add', 'MemberBillingController@glje_add')->name('mybillings.glje_add');
            Route::post('billing_glje/create', 'MemberBillingController@glje_create')->name('mybillings.glje_create');
            Route::get('billing_glje/{id_glje}', 'MemberBillingController@glje_show')->name('mybillings.glje_show');
            Route::get('billing_glje/{id_glje}/edit', 'MemberBillingController@glje_edit')->name('mybillings.glje_edit');
            Route::post('billing_glje/{id_glje}/update', 'MemberBillingController@glje_update')->name('mybillings.glje_update');
            Route::post('billing_glje/{id_glje}/update_no', 'MemberBillingController@glje_update_no')->name('mybillings.glje_update_no');
            Route::get('billing_glje/{id_glje}/download', 'MemberBillingController@glje_download')->name('mybillings.glje_download');
            Route::get('billing_glje/{id_glje}/delete', 'MemberBillingController@glje_delete')->name('mybillings.glje_delete');

            Route::get('profile', 'MemberProfileController@show')->name('myprofile.show');
            Route::post('profile/update', 'MemberProfileController@update')->name('myprofile.update');
            Route::get('service_list/show_as_json/{id_service}', 'ServiceListController@show_as_json')->name('service_list.show_as_json');
            Route::get('pricelist', 'MemberPricelistController@index')->name('mypricelist.index');

            Route::get('report', 'MemberReportController@index')->name('myreport.index');
            Route::get('report/detail', 'MemberReportController@detail')->name('myreport.detail');
            Route::get('report/my_project', 'MemberReportController@my_project')->name('myreport.my_project');
            Route::post('report/detail', 'MemberReportController@detail_post')->name('myreport.detail_post');
            Route::get('report/timeliness', 'MemberReportController@timeliness')->name('myreport.timeliness');
            Route::post('report/timeliness', 'MemberReportController@timeliness_post')->name('myreport.timeliness_post');
            Route::get('report/timeliness_detail', 'MemberReportController@timeliness_detail')->name('myreport.timeliness_detail');
            Route::any('report/timeliness_detail/data', 'MemberReportController@timeliness_detail_list')->name('myreport.timeliness_detail_list');
            Route::post('report/timeliness_detail', 'MemberReportController@timeliness_detail_post')->name('myreport.timeliness_detail_post');
            Route::get('report/coa', 'MemberReportController@coa')->name('myreport.coa');
            Route::get('report/coa/Excel/{date1}/{date2}', 'MemberReportController@coa_to_excel')->name('myreport.coa.excel');
            Route::any('report/coa/data', 'MemberReportController@coa_list')->name('myreport.coa_list');
            Route::get('report/user_registration', 'MemberReportController@user_registration')->name('myreport.user_registration');
            Route::any('report/user_registration/data', 'MemberReportController@user_registration_list')->name('myreport.user_registration_list');
            Route::get('report/workload_analysis', 'MemberReportController@workload_analysis')->name('myreport.workload_analysis');
            Route::any('report/workload_analysis/data', 'MemberReportController@workload_analysis_list')->name('myreport.workload_analysis_list');
            Route::get('report/performance', 'MemberReportController@performance')->name('myreport.performance');
            Route::any('report/performance/data', 'MemberReportController@performance_list')->name('myreport.performance_list');
            Route::get('report/critical_service', 'MemberReportController@critical_service')->name('myreport.critical_service');
            Route::post('report/critical_service', 'MemberReportController@critical_service_post')->name('myreport.critical_service_post');
            Route::get('report/service_cost', 'MemberReportController@service_cost')->name('myreport.service_cost');
            Route::post('report/service_cost', 'MemberReportController@service_cost_post')->name('myreport.service_cost_post');
            Route::get('report/service_workload', 'MemberReportController@service_workload')->name('myreport.service_workload');
            Route::post('report/service_workload', 'MemberReportController@service_workload_post')->name('myreport.service_workload_post');
            Route::get('report/search_engine', 'MemberReportController@search_engine')->name('myreport.search_engine');
            Route::any('report/search_engine/data', 'MemberReportController@search_engine_list')->name('myreport.search_engine_list');
            Route::get('report/invoice_issue', 'MemberReportController@invoice_issue')->name('myreport.invoice_issue');
            Route::any('report/invoice_issue/data', 'MemberReportController@invoice_issue_list')->name('myreport.invoice_issue_list');
            Route::get('report/dsa_advance', 'MemberReportController@dsa_advance')->name('myreport.dsa_advance');
            Route::any('report/dsa_advance/data', 'MemberReportController@dsa_advance_list')->name('myreport.dsa_advance_list');
            Route::any('report/dsa_advance_null/data', 'MemberReportController@dsa_advance_null_list')->name('myreport.dsa_advance_null_list');
            Route::get('report/complete_ticket', 'MemberReportController@complete_ticket')->name('myreport.complete_ticket');
            Route::any('report/complete_ticket/data', 'MemberReportController@complete_ticket_list')->name('myreport.complete_ticket_list');

        });
        Route::get('static/faq', 'StaticPageController@faq')->name('static_page.faq');
        Route::get('static/help', 'StaticPageController@help')->name('static_page.help');
        Route::get('static/contact', 'StaticPageController@contact')->name('static_page.contact');
        Route::get('static/privacy', 'StaticPageController@privacy')->name('static_page.privacy');
        Route::get('static/legal', 'StaticPageController@legal')->name('static_page.legal');

        # Internal API
        Route::get('coa/search_projects', 'MemberRequestController@get_projects')->name('myrequests.get_projects');
        Route::get('coa/search_by_project/{project}', 'MemberRequestController@get_coa_by_project')->name('myrequests.get_coa_by_project');
        # Datatables
        Route::any('request/data/ongoing_search', 'MemberRequestController@ongoing_search')->name('myrequests.ongoing_search');
        Route::any('request/data/ongoing_search_home', 'MemberRequestController@ongoing_search_home')->name('myrequests.ongoing_search_home');
        Route::any('request/data/myrequest_summary_by_agency', 'MemberRequestController@myrequest_summary_by_agency')->name('myrequests.myrequest_summary_by_agency');
        Route::any('request/data/old_ongoing_request_search', 'MemberRequestController@old_ongoing_request_search')->name('myrequests.old_ongoing_request_search');
        Route::any('request/data/ongoing_request_search', 'MemberRequestController@ongoing_request_search')->name('myrequests.ongoing_request_search');
        Route::any('request/data/history_request_search', 'MemberRequestController@history_request_search')->name('myrequests.history_request_search');
        Route::any('request/data/tracking_request_search/{id}', 'MemberRequestController@tracking_request_search')->name('myrequests.tracking_request_search');
        Route::any('billing/data/list', 'MemberBillingController@billing_list')->name('mybillings.billing_list');
        Route::get('billing/data/transaction_ready_to_bill/{id_agency_unit}', 'MemberBillingController@transaction_ready_to_bill')->name('mybillings.transaction_ready_to_bill');
        Route::any('pricelist/data/list', 'MemberPricelistController@pricelist')->name('mypricelist.list');
        Route::any('service/data/list_new', 'MemberServiceController@list_new')->name('myservices.list_new');
        Route::any('service/data/list_ongoing', 'MemberServiceController@list_ongoing')->name('myservices.list_ongoing');
        Route::any('service/data/list_ongoing_new', 'MemberServiceController@list_ongoing_new')->name('myservices.list_ongoing_new');
        Route::any('service/data/list_document', 'MemberServiceController@list_document')->name('myservices.list_document');
        Route::post('service/data/detail_document', 'MemberServiceController@detail_document')->name('myservices.detail_document');
        Route::any('service/data/list_tracking', 'MemberServiceController@list_tracking')->name('myservices.list_tracking');
        Route::any('service/data/restore_request_search', 'MemberServiceController@restore_request_search')->name('myservices.restore_request_search');
        Route::get('service/{id_transaction}/restore', 'MemberServiceController@restore_transaction')->name('myservices.restore');
    });
});
