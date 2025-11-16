<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticPageController extends Controller
{
 	public function help(){
 		$data['title'] = 'HELP';
    $data['breadcrumps'] = ['Member Area', $data['title']];
    return view('member.static_page.help', $data);		
 	}

 	public function faq(){
 		$data['title'] = 'FAQ';
    $data['breadcrumps'] = ['Member Area', $data['title']];
    return view('member.static_page.faq', $data);		
 	}

 	public function contact(){
 		$data['title'] = 'Contact Admin';
    $data['breadcrumps'] = ['Member Area', $data['title']];
    return view('member.static_page.contact', $data);		
 	}

	 public function legal(){
		$data['title'] = 'Legal';
   $data['breadcrumps'] = ['Member Area', $data['title']];
   return view('member.static_page.legal', $data);		
	}

	public function privacy(){
		$data['title'] = 'Privacy';
   $data['breadcrumps'] = ['Member Area', $data['title']];
   return view('member.static_page.legal', $data);		
	}
}
