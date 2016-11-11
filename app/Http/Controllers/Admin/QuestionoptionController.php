<?php
/******************************************
****AuThor:rubbish.boy@163.com
****Title :题目选项
*******************************************/
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Model\Question;
use App\Http\Model\Questionoption;
use DB;
use URL;
use Cache;
use App\Common\lib\Cates; 
class QuestionoptionController extends PublicController
{
    //
    /******************************************
	****AuThor:rubbish.boy@163.com
	****Title :列表
	*******************************************/
	public function index($id)  
	{
		$website=$this->website;
        $website['modelname']=getCurrentControllerName();
		$website['cursitename']=trans('admin.website_navigation_question_option');
		$website['apiurl_list']=URL::action('Admin\QuestionoptionController@api_list');
		$website['apiurl_one_action']=URL::action('Admin\OneactionapiController@api_one_action');
		$website['apiurl_delete']=URL::action('Admin\DeleteapiController@api_delete');
		$website['apiurl_cache']=URL::action('Admin\CacheapiController@api_cache');
		$website['link_add']=URL::action('Admin\QuestionoptionController@add').'/'.$id;
		$website['link_edit']=route('get.admin.questionoption.edit').'/';
		$website['way']='title';
		$wayoption[]=array('text'=>trans('admin.website_questionoption_item_title'),'value'=>'title');
		$website['wayoption']=json_encode($wayoption);
		$info = object_array(DB::table('questions')->whereId($id)->first());
		$website['link_back']=route('get.admin.question').'/'.$info['type'];
		$website['info']=$info;
		$website['qid']=$id;
		return view('admin/questionoption/index')->with('website',$website);
	}
    /******************************************
	****AuThor:rubbish.boy@163.com
	****Title :添加
	*******************************************/
	public function add($id)
	{
		$website=$this->website;
        $website['modelname']=getCurrentControllerName();
		$website['cursitename']=trans('admin.website_navigation_question_option');
		$website['apiurl_add']=URL::action('Admin\QuestionoptionController@api_add');
		$website['apiurl_info']=URL::action('Admin\QuestionoptionController@api_info');
		$website['apiurl_edit']=URL::action('Admin\QuestionoptionController@api_edit');
		$website['apiurl_del_image']=URL::action('Admin\DeleteapiController@api_del_image');
		$website['id']=0;
		$info = object_array(DB::table('questions')->whereId($id)->first());
		$website['info']=$info;
		$website['qid']=$id;
		return view('admin/questionoption/add')->with('website',$website);
	}
    /******************************************
	****AuThor : rubbish.boy@163.com
	****Title  : 编辑信息
	*******************************************/
	public function edit($id)  
	{
		$website=$this->website;
        $website['modelname']=getCurrentControllerName();
		$website['cursitename']=trans('admin.website_navigation_question_option');
		$website['apiurl_add']=URL::action('Admin\QuestionoptionController@api_add');
		$website['apiurl_info']=URL::action('Admin\QuestionoptionController@api_info');
		$website['apiurl_edit']=URL::action('Admin\QuestionoptionController@api_edit');
		$website['apiurl_del_image']=URL::action('Admin\DeleteapiController@api_del_image');
		$website['id']=$id;
		$info = object_array(DB::table('questionoptions')->whereId($id)->first());
		$website['qid']=$info['qid'];
		$qinfo= object_array(DB::table('questions')->whereId($info['qid'])->first());
		$website['info']=$qinfo;

		return view('admin/questionoption/add')->with('website',$website);
	}
    /******************************************
	****AuThor:rubbish.boy@163.com
	****Title :列表接口
	*******************************************/
	public function api_list(Request $request)  
	{
		$qid=$request->get('qid');
		$search_field=$request->get('way')?$request->get('way'):'name';
		$keyword=$request->get('keyword');
		if($keyword)
		{
			$list=Question::find($qid)->hasManyQuestionoptions()->where($search_field, 'like', '%'.$keyword.'%')->paginate($this->pagesize);
			//分页传参数
			$list->appends(['keyword' => $keyword,'way' =>$search_field,'qid'=>$qid])->links();
		}
		else
		{
			$list=Question::find($qid)->hasManyQuestionoptions()->paginate($this->pagesize);
			$list->appends(['qid'=>$qid])->links();
		}
		if($list)
		{
			$msg_array['status']='1';
			$msg_array['info']=trans('admin.website_get_success');
			$msg_array['is_reload']=0;
			$msg_array['curl']='';
			$msg_array['resource']=$list;
			$msg_array['param_way']=$search_field;
			$msg_array['param_keyword']=$keyword;
		}
		else
		{
			$msg_array['status']='1';
			$msg_array['info']=trans('admin.website_get_empty');
			$msg_array['is_reload']=0;
			$msg_array['curl']='';
			$msg_array['resource']="";
			$msg_array['param_way']=$search_field;
			$msg_array['param_keyword']=$keyword;
		}
        return response()->json($msg_array);
	}
    /******************************************
	****AuThor:rubbish.boy@163.com
	****Title :添加接口
	*******************************************/
	public function api_add(Request $request)  
	{

		$params = new Questionoption;
		$params->qid 		= $request->get('qid');
		$params->title 		= $request->get('title');
		$params->is_answer	= $request->get('is_answer');
		$params->status		= $request->get('status');
		$params->user_id	= $this->user['id'];

		//图片上传处理接口
		$attachment='attachment';
		$data_image=$request->get($attachment);
		if($data_image)
		{
			//上传文件归类：获取控制器名称
			$classname=getCurrentControllerName();
			$params->attachment=$this->uploads_action($classname,$data_image);
			$params->isattach=1;
		}

		if ($params->save()) 
		{
			$msg_array['status']='1';
			$msg_array['info']=trans('admin.website_add_success');
			$msg_array['is_reload']=0;
			$msg_array['curl']=URL::action('Admin\QuestionoptionController@index').'/'.$params->qid;
			$msg_array['resource']='';
			$msg_array['param_way']='';
			$msg_array['param_keyword']='';
		} 
		else 
		{
			$msg_array['status']='0';
			$msg_array['info']=trans('admin.website_add_failure');
			$msg_array['is_reload']=0;
			$msg_array['curl']='';
			$msg_array['resource']="";
			$msg_array['param_way']='';
			$msg_array['param_keyword']='';	

		}	

        return response()->json($msg_array);
	}
    /******************************************
	****AuThor:rubbish.boy@163.com
	****Title :详情接口
	*******************************************/
	public function api_info(Request $request)  
	{

		$condition['id']=$request->get('id');
		$info=DB::table('questionoptions')->where($condition)->first();
		if($info)
		{
			$msg_array['status']='1';
			$msg_array['info']=trans('admin.website_get_success');
			$msg_array['is_reload']=0;
			$msg_array['curl']='';
			$msg_array['resource']=$info;
			$msg_array['param_way']='';
			$msg_array['param_keyword']='';
		}
		else
		{
			$msg_array['status']='0';
			$msg_array['info']=trans('admin.website_get_empty');
			$msg_array['is_reload']=0;
			$msg_array['curl']='';
			$msg_array['resource']="";
			$msg_array['param_way']='';
			$msg_array['param_keyword']='';
		}
        return response()->json($msg_array);
	}
    /******************************************
	****@AuThor : rubbish.boy@163.com
	****@Title  : 更新数据接口
	****@return : Response
	*******************************************/
	public function api_edit(Request $request)
	{

		$params = Questionoption::find($request->get('id'));
		$params->title 		= $request->get('title');
		$params->is_answer	= $request->get('is_answer');
		$params->status		= $request->get('status');

		//图片上传处理接口
		$attachment='attachment';
		$data_image=$request->get($attachment);
		if($data_image)
		{
			//上传文件归类：获取控制器名称
			$classname=getCurrentControllerName();
			$params->attachment=$this->uploads_action($classname,$data_image);
			$params->isattach=1;
		}

		if ($params->save()) 
		{
			$msg_array['status']='1';
			$msg_array['info']=trans('admin.website_save_success');
			$msg_array['is_reload']=0;
			$msg_array['curl']=URL::action('Admin\QuestionoptionController@index').'/'.$params->qid;
			$msg_array['resource']='';
			$msg_array['param_way']='';
			$msg_array['param_keyword']='';
		} 
		else 
		{
			$msg_array['status']='0';
			$msg_array['info']=trans('admin.website_save_failure');
			$msg_array['is_reload']=0;
			$msg_array['curl']='';
			$msg_array['resource']="";
			$msg_array['param_way']='';
			$msg_array['param_keyword']='';	
		}
		return response()->json($msg_array);
	}
}