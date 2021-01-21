<?php

namespace App\Http\Controllers\Web\Admin;

use App\Model\Sample;
use App\Model\Service;
use App\Model\ServiceLog;
use App\Model\Microscopy;
use App\Model\LCFlaggedMGIT;
use App\Model\CultureInoculation;
use App\Model\RequestServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;

class LCFlaggedMGITController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data = [];
        /*$data['sample'] = ServiceLog::select(DB::raw('DISTINCT(t_service_log.id)  AS ID'),'m.enroll_id','m.id as sample_id', 'm.receive_date as receive',
		  'm.test_reason as reason','is_accepted','s.result','t_service_log.sample_label as samples','t_service_log.enroll_label as enroll_label',
		  't_service_log.service_id','t_service_log.id as log_id', 't_service_log.status','m.no_of_samples','t.status as dna_status',
		  't.created_at as date_of_extraction','t_service_log.mgit','t_service_log.tube_id_lj','t_service_log.tube_id_lc','ci.inoculation_date', 
		  't_service_log.gu','lfm.flagging_date','m.fu_month','t_service_log.tag','t_service_log.enroll_id AS enrollID','t_service_log.sample_id AS sampleID',
		  't_service_log.rec_flag')
        ->leftjoin('sample as m','m.id','=','t_service_log.sample_id')
        ->leftjoin('t_dnaextraction as t', function ($join) {
              $join->on('t.sample_id','=','t_service_log.sample_id');
			  $join->on('t.tag', '=','t_service_log.tag')
                   ->where('t.status', 1);
          })
        ->leftjoin('t_microscopy as s', function ($join) {
              $join->on('s.sample_id','=','t_service_log.sample_id')
                   ->where('s.status', 1);
          })
        ->leftjoin('t_culture_inoculation as ci','ci.sample_id','=','t_service_log.sample_id')
        ->leftjoin('t_lc_flagged_mgit as lfm','lfm.sample_id','=','t_service_log.sample_id')
        ->where('t_service_log.service_id',17)
        ->whereIn('t_service_log.status',[1]) //        ->whereIn('t_service_log.status',[0,1,2])
        ->orderBy('enroll_id','desc')
		//->toSql();
        ->get();







        //dd($data['sample'] );
        foreach ($data['sample'] as $key => $value) {
          $lpa = ServiceLog::select('service_id')->where('enroll_id',$value->enroll_id)->where('sample_id',$value->sample_id)->first();
          if($lpa->service_id==6){
            $value->lpa_type = "LJ";
          }elseif($lpa->service_id==7){
            $value->lpa_type = "LC";
          }elseif($lpa->service_id==13){
            $value->lpa_type = "Both";
          }else{
            $value->lpa_type = "NA";
          }
        }
        //dd($data['sample']);
		
		
		//dd(Config::get('m_services_array.tests'));			
			 foreach($data['sample'] as $sampledata){
				//echo $sampledata->enroll_id; die;
				
				$services=RequestServices::select('service_id','enroll_id')->where('enroll_id',$sampledata->enroll_id)->get();
				//dd($services);
				$data['test_requested['.$sampledata->enroll_id.']']='';
				$data['services_col_color['.$sampledata->enroll_id.']']='N';
				if(!$services->isEmpty()){ //echo "hi"; die;					
					//$result[]='';
					
					unset($result);//reinitialize array
					foreach($services as $serv){
						$result[] = Config::get('m_services_array.tests')[$serv->service_id] ?? null;						
					}
					//dd($result);
					//dd(count($result));
					// comma in the array 
                    $data['test_requested['.$sampledata->enroll_id.']'] = implode(', ', $result); 
					//dd($data);
					//For display green colour for more than 1 services
					if(count($result)>1)
					{
						$data['services_col_color['.$sampledata->enroll_id.']']='Y';
					}				
					
				}
			 }*/
        return view('admin.lc_flagged_mgit.list',compact('data'));

  }
	public function ajaxLCFlaggedMGITList(Request $request){
		 //dd($request->all());
		
		## Read value
		$draw = $_POST['draw'];
		$row = $_POST['start'];
		$rowperpage = $_POST['length']; // Rows display per page
		$columnIndex = $_POST['order'][0]['column']; // Column index
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
		$searchValue = $_POST['search']['value']; // Search value  
        //dd($request->all());
		## Search 
		$searchQuery = "";
        if($searchValue != ''){
			   $searchQuery .= " and (t_service_log.sample_label like '%".$searchValue."%' OR t_service_log.mgit like '%".$searchValue."%') ";
			  
		}
		
        ## Total number of records without filtering
		//DB::enableQueryLog();	
        /*$sel=DB::select("SELECT IFNULL(COUNT(distinct t_service_log.id),0) AS count
                FROM `t_service_log` 
                LEFT JOIN `sample` AS `m` ON `m`.`id` = `t_service_log`.`sample_id` 
                LEFT JOIN `t_dnaextraction` AS `t` ON `t`.`sample_id` = `t_service_log`.`sample_id` 
                AND `t`.`tag` = `t_service_log`.`tag` AND `t`.`status` = 1 
                LEFT JOIN `t_microscopy` AS `s` ON `s`.`sample_id` = `t_service_log`.`sample_id` AND `s`.`status` = 1 
                LEFT JOIN `t_culture_inoculation` AS `ci` ON `ci`.`sample_id` = `t_service_log`.`sample_id` 
                LEFT JOIN `t_lc_flagged_mgit` AS `lfm` ON `lfm`.`sample_id` = `t_service_log`.`sample_id` 
                WHERE `t_service_log`.`service_id` =17 AND `t_service_log`.`status` IN (1)");*/
		
		$sel=DB::select("SELECT IFNULL(COUNT(distinct t_service_log.id),0) AS count
                FROM `t_service_log`                 
                WHERE `t_service_log`.`service_id` =17 AND `t_service_log`.`status` IN (1)");
				
        //dd(DB::getQueryLog());
        //echo "<pre>"; print_r($sel);	echo "</pre>";	die();
	    $totalRecords =$sel[0]->count;
		//dd('totalRecords'.$totalRecords);	
		
        ## Total number of records with filtering
        //DB::enableQueryLog();			
		/*$sel=DB::select("SELECT IFNULL(COUNT(distinct t_service_log.id),0) AS count_filtered
                FROM `t_service_log` 
                LEFT JOIN `sample` AS `m` ON `m`.`id` = `t_service_log`.`sample_id` 
                LEFT JOIN `t_dnaextraction` AS `t` ON `t`.`sample_id` = `t_service_log`.`sample_id` 
                AND `t`.`tag` = `t_service_log`.`tag` AND `t`.`status` = 1 
                LEFT JOIN `t_microscopy` AS `s` ON `s`.`sample_id` = `t_service_log`.`sample_id` AND `s`.`status` = 1 
                LEFT JOIN `t_culture_inoculation` AS `ci` ON `ci`.`sample_id` = `t_service_log`.`sample_id` 
                LEFT JOIN `t_lc_flagged_mgit` AS `lfm` ON `lfm`.`sample_id` = `t_service_log`.`sample_id` 
                WHERE `t_service_log`.`service_id` =17 AND `t_service_log`.`status` IN (1) ".$searchQuery);*/
		 $sel=DB::select("SELECT IFNULL(COUNT(distinct t_service_log.id),0) AS count_filtered
                FROM `t_service_log`                 
                WHERE `t_service_log`.`service_id` =17 AND `t_service_log`.`status` IN (1) ".$searchQuery);		
				
		//dd(DB::getQueryLog());			  
		$totalRecordwithFilter = $sel[0]->count_filtered;
		//dd('totalRecordwithFilter'.$totalRecordwithFilter);
		## Fetch records
		//DB::enableQueryLog();
				
		$lcflaggedQry=DB::select("select DISTINCT(t_service_log.id)  AS ID, `m`.`enroll_id`, `m`.`id` as `sample_id`, 
		`m`.`receive_date` as `receive`, `m`.`test_reason` as `reason`, `is_accepted`, `s`.`result`, 
		`t_service_log`.`sample_label` as `samples`, `t_service_log`.`enroll_label` as `enroll_label`,
        `t_service_log`.`service_id`, `t_service_log`.`id` as `log_id`, `t_service_log`.`status`, `m`.`no_of_samples`, 
        `t`.`status` as `dna_status`, `t`.`created_at` as `date_of_extraction`, `t_service_log`.`mgit`, `t_service_log`.`tube_id_lj`,
        `t_service_log`.`tube_id_lc`, `ci`.`inoculation_date`, `t_service_log`.`gu`,`lfm`.`flagging_date`, `m`.`fu_month`,
        `t_service_log`.`tag`, `t_service_log`.`enroll_id` as `enrollID`, `t_service_log`.`sample_id` as `sampleID`, 
		`t_service_log`.`rec_flag` 
        from `t_service_log` 
        left join `sample` as `m` on `m`.`id` = `t_service_log`.`sample_id` 
        left join `t_dnaextraction` as `t` on `t`.`sample_id` = `t_service_log`.`sample_id` 
        and `t`.`tag` = `t_service_log`.`tag` and `t`.`status` = 1 
        left join `t_microscopy` as `s` on `s`.`sample_id` = `t_service_log`.`sample_id` and `s`.`status` = 1 
        left join `t_culture_inoculation` as `ci` on `ci`.`sample_id` = `t_service_log`.`sample_id` 
        left join `t_lc_flagged_mgit` as `lfm` on `lfm`.`sample_id` = `t_service_log`.`sample_id` 
        where `t_service_log`.`service_id` =17 and `t_service_log`.`status` in (1) ".$searchQuery." 
        order by `t_service_log`.`enroll_id` desc limit ".$row.",".$rowperpage);	    
        			
		//dd($lcflaggedQry);
		//dd(DB::getQueryLog());
		foreach ($lcflaggedQry as $key => $value) {
          $lpa = ServiceLog::select('service_id')->where('enroll_id',$value->enroll_id)->where('sample_id',$value->sample_id)->first();
          if($lpa->service_id==6){
            $value->lpa_type = "LJ";
          }elseif($lpa->service_id==7){
            $value->lpa_type = "LC";
          }elseif($lpa->service_id==13){
            $value->lpa_type = "Both";
          }else{
            $value->lpa_type = "NA";
          }
        }
		
		$test_requested = array();
		$services_col_color=array();
		//$reqServ_service_id=array();
		foreach($lcflaggedQry as $sampledata){
                //echo $sampledata->enroll_id; die;
                $services=RequestServices::select('service_id','enroll_id')->where('enroll_id',$sampledata->enroll_id)->get();
				//dd($services);
				$test_requested[$sampledata->enroll_id]='';
				$services_col_color[$sampledata->enroll_id]='N';
				if(!$services->isEmpty()){ //echo "hi"; die;					
					//$result[]='';
					
					unset($result);//reinitialize array
					foreach($services as $serv){
						$result[] = Config::get('m_services_array.tests')[$serv->service_id] ?? null;						
					}
					//dd($result);
					//dd(count($result));
					// comma in the array 
                    $test_requested[$sampledata->enroll_id] = implode(', ', $result); 
					//dd($data);
					//For display green colour for more than 1 services
					if(count($result)>1)
					{
						$services_col_color[$sampledata->enroll_id]='Y';
					}				
					
				}
        }
		//dd($test_requested);
		//dd($reqServ_service_id);
	 
      
		
		$data = array();		
		$tdStyle="";
		$submitBtn="";		
		foreach($lcflaggedQry as $key=>$samples){
			
				$tdStyle=$services_col_color[$samples->enroll_id]=="Y"?'style="background-color:#ccffcc;width:100%;height:100%;display:block;"':"";
				
				if($samples->status==1){
				   $submitBtn="<button type='button' onclick=\"openForm( '".$samples->samples."','".$samples->log_id."','".$samples->lpa_type."','".$samples->gu."','".$samples->flagging_date."','".$samples->tag."', '".$samples->enrollID."','".$samples->sampleID."','".$samples->service_id."',".$samples->rec_flag.");\" class='btn btn-info btn-sm resultbtn' >submit</button>";
				}else{
				  $submitBtn="Done";	
				}
				
				$data[] = array(
				     "DT_RowId"=> $key,
					 "DT_RowClass"=>'sel ',
				     "ID"=>$samples->ID,
					 "sample_id"=>$samples->samples,
					 "mgit_tube_seq_id"=>$samples->mgit,
					 "date_of_inocculation"=>date('d-m-Y', strtotime($samples->inoculation_date)),
					 "sample_result"=>$samples->result,					 
					 "test_requested"=>'<span '.$tdStyle.' >'.$test_requested[$samples->enroll_id].'</span>',					 
					 "reason_for_test"=>$samples->reason,
					 "follow_up_month"=>$samples->fu_month,
					 "gu"=>$samples->gu,
					 "flagging_date"=>$samples->flagging_date,
					 "submit_btn"=>$submitBtn,
				   );
			
		}	
			

		## Response
		$response = array(
		  "draw" => intval($draw),
		  "iTotalRecords" => $totalRecords,
		  "iTotalDisplayRecords" => $totalRecordwithFilter,
		  "aaData" => $data
		);
		echo json_encode($response);
	}	  
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       //dd($request->all());
	  $success = true;
	  DB::beginTransaction();
      try {
		$logdata = ServiceLog::find($request->log_id);

        //LCFlaggedMGIT::where('sample_id',$logdata->sample_id)->delete(); //for repeataton of any sample
		LCFlaggedMGIT::where('enroll_id',$logdata->enroll_id)->delete(); //for repeataton of any sample
        $data = LCFlaggedMGIT::create([
          'sample_id' => $logdata->sample_id,
          'enroll_id' => $logdata->enroll_id,
          'gu' => $request->gu,
          'flagging_date' => $request->flagging_date,
          'created_by' => $request->user()->id,
          'updated_by' => $request->user()->id
        ]);
		
        $logdata->comments=$request->comments;
        $logdata->tested_by=$request->user()->name;
        $logdata->released_dt=date('Y-m-d');
        $logdata->status = 0;
        if(!empty($request->gu)){
        $logdata->gu = $request->gu;
        }
        $logdata->save();
		
        $new_service = [
          'enroll_id' => $logdata->enroll_id,
          'sample_id' => $logdata->sample_id,
          'service_id' => 18,
          'status' => 1,
          'created_by' => $request->user()->id,
          'updated_by' => $request->user()->id,
          'reported_dt'=>date('Y-m-d'),
		  'tag' => $request->tagId,
		  'rec_flag' => $request->rec_flag,
          'enroll_label' => $logdata->enroll_label,
          'sample_label' => $logdata->sample_label,
        ];

        $nwService = ServiceLog::create($new_service);
		DB::commit();		
		 }catch(\Exception $e){ 
		  
			  //dd($e->getMessage());
			  $error = $e->getMessage();		  
			  DB::rollback(); 
			  $success = false;
			   
		}
		 if($success){
			// Return data for successful delete
			 return redirect('/lc_flagged_mgit');
		}else{
			 return redirect('/lc_flagged_mgit')->withErrors(['Sorry!! Action already taken of the selected Sample']);
		}
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

     public function lcflagprint()
    {
        $data = [];
          $data['sample'] = ServiceLog::select('m.enroll_id','m.id as sample_id', 'm.receive_date as receive','m.test_reason as reason','is_accepted','s.result','t_service_log.sample_label as samples','t_service_log.enroll_label as enroll_label','t_service_log.service_id','t_service_log.id as log_id', 't_service_log.status','m.no_of_samples','t.status as dna_status','t.created_at as date_of_extraction','ci.mgit_id','ci.tube_id_lj','ci.tube_id_lc','ci.inoculation_date', 'lfm.gu','lfm.flagging_date')
        ->leftjoin('sample as m','m.id','=','t_service_log.sample_id')
        ->leftjoin('t_dnaextraction as t', function ($join) {
              $join->on('t.sample_id','=','t_service_log.sample_id')
                   ->where('t.status', 1);
          })
        ->leftjoin('t_microscopy as s','s.sample_id','=','t_service_log.sample_id')
        ->leftjoin('t_culture_inoculation as ci','ci.sample_id','=','t_service_log.sample_id')
        ->leftjoin('t_lc_flagged_mgit as lfm','lfm.sample_id','=','t_service_log.sample_id')
        ->where('t_service_log.service_id',17)
        ->where('s.status',1)
        ->whereIn('t_service_log.status',[1,2])
        ->orderBy('enroll_id','desc')
        ->get();
        foreach ($data['sample'] as $key => $value) {
          $lpa = ServiceLog::select('service_id')->where('enroll_id',$value->enroll_id)->where('sample_id',$value->sample_id)->first();
          if($lpa->service_id==6){
            $value->lpa_type = "LJ";
          }elseif($lpa->service_id==7){
            $value->lpa_type = "LC";
          }elseif($lpa->service_id==13){
            $value->lpa_type = "Both";
          }else{
            $value->lpa_type = "NA";
          }
        }
        //dd($data['sample']);
        return view('admin.lc_flagged_mgit.print',compact('data'));

    }
}