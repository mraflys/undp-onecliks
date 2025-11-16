<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\SecRole;
use App\ServiceList;
use App\Country;
use App\SecUser;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class SoftDeletedUserController extends Controller
{
  protected $table = 'sec_user';

  public function index(){
    $data['title'] = 'Soft Deleted User';
    $data['breadcrumps'] = ['Master', 'User'];
    $data['with_deleted'] = true;
    return view('admin.user.list', $data);
  }

  public function revive($id) {
    $sec_user = SecUser::withTrashed()->find($id);
    $sec_user->date_deleted = null;
    $sec_user->save();
    return redirect()->route('users.index');
  }
}
