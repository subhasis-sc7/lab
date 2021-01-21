<?php

namespace App\Http\Controllers\Web\Admin;

use App\Model\LJ;
use App\Model\Sample;
use App\Model\Service;
use App\Model\ServiceLog;
use App\Model\LJWeekLog;
use App\Model\LJDetail;
use App\Model\Microscopy;
use App\Model\LCFlaggedMGIT;
use App\Model\CultureInoculation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class LJController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      try{

        $data = [];
          $data['sample'] = ServiceLog::select('m.enroll_id','m.id as sample_id', 'm.receive_date as receive','m.test_reason as reason','is_accepted','s.result','t_service_log.sample_label as samples','t_service_log.enroll_label as enroll_label','t_service_log.service_id','t_service_log.id as log_id', 't_service_log.status','m.no_of_samples', 'ci.mgit_id','ci.tube_id_lj','ci.tube_id_lc','ci.inoculation_date')
        ->leftjoin('sample as m','m.id','=','t_service_log.sample_id')
        ->leftjoin('t_culture_inoculation as ci','ci.sample_id','=','t_service_log.sample_id')
                ->leftjoin('t_lj_detail as tljd','tljd.sample_id','=','t_service_log.sample_id')
        ->leftjoin('t_microscopy as s', function ($join) {
              $join->on('s.sample_id','=','t_service_log.sample_id')
                   ->where('s.status', 1);
          })
        ->where('t_service_log.service_id',20)
        ->whereIn('t_service_log.status',[1]) //    ->whereIn('t_service_log.status',[0,-1,1])
        ->orderBy('enroll_id','desc')
        ->get();

// dd($data['sample']);


        $data['week'] = 0;
        $data['weeks'] = ["--Select--","Week 1","Week 2","Week 3","Week 4","Week 5","Week 6","Week 7","Week 8"];
// dd($data['sample']);
        return view('admin.lj.list',compact('data'));


      }catch(\Exception $e){
          $error = $e->getMessage();
          return view('admin.layout.error',$error);   // insert query
      }
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
	    DB::beginTransaction();
        try {

            if ($request->editresult) {

                $sample = Sample::select('id', 'enroll_id')->where('sample_label', $request->sample_id)->first();
                if ($sample)
                    $ljobj = LJDetail::select('id')->orderBy('id', 'desc')->where('sample_id', $sample->id)->first();
                if ($ljobj) {
                    $lj = LJDetail::find($ljobj->id);
                    $lj->test_id = $request->test_id;
                    $lj->culture_smear = $request->culture_smear;
                    $lj->final_result = $request->final_result;
                    $lj->lj_result_date = $request->lj_result_date;
					$lj->species = $request->final_result=="NTM"?$request->species:"";
                    $lj->other_result = $request->final_result=="Other Result"?$request->other_result:"";
                    $lj->reason_edit = $request->reason_edit;
                    $lj->created_by = $request->user()->id;
                    $lj->is_moved = 0;
                    $lj->save();
                }
				 DB::commit();
                return redirect("/microbiologist");
            }
            // dd($request->all());
            // dd($request->all());
            $data = [];
            $data['sample'] = ServiceLog::select('m.enroll_id', 'm.id as sample_id', 'm.receive_date as receive', 'm.test_reason as reason', 'is_accepted', 's.result', 't_service_log.sample_label as samples', 't_service_log.enroll_label as enroll_label', 't_service_log.service_id', 't_service_log.id as log_id', 't_service_log.status', 'm.no_of_samples', 'ci.mgit_id', 'ci.tube_id_lj', 'ci.tube_id_lc', 'ci.inoculation_date')
                ->leftjoin('sample as m', 'm.id', '=', 't_service_log.sample_id')
                // ->leftjoin('t_lj_week_log as tljd','tljd.sample_id','=','t_service_log.sample_id')
                //->leftjoin('t_microscopy as s','s.sample_id','=','t_service_log.sample_id')
                ->leftjoin('t_microscopy as s', function ($join) {
                    $join->on('s.sample_id', '=', 't_service_log.sample_id')
                        ->where('s.status', 1);
                })
                ->leftjoin('t_culture_inoculation as ci', 'ci.sample_id', '=', 't_service_log.sample_id')
                ->where('t_service_log.service_id', 20)
                ->whereIn('t_service_log.status', [0, 1, -1, 2, 3, 4, 5, 6, 7, 8])
                ->where('t_service_log.week_log', $request->week)
                ->orderBy('enroll_id', 'desc')
                ->get();


            // dd($data['sample']);

            $data['week'] = $request->week;
            $data['weeks'] = ["--Select--", "Week 1", "Week 2", "Week 3", "Week 4", "Week 5", "Week 6", "Week 7", "Week 8"];
            
            DB::commit();
            return view('admin.lj.list', compact('data'));			
	    } catch (\Exception $e) {
			 //dd($e->getMessage());
            $error = $e->getMessage();
            return view('admin.layout.error', $error);  
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

        try {

            $data = [];
            $data['sample'] = ServiceLog::select('m.enroll_id', 'tld.test_id', 'tld.culture_smear', 'm.id as sample_id', 'm.receive_date as receive', 
			'm.test_reason as reason', 'is_accepted', 's.result', 't_service_log.sample_label as samples', 't_service_log.enroll_label as enroll_label',
			't_service_log.service_id', 't_service_log.id as log_id', 't_service_log.status', 't_service_log.week_log', 'm.no_of_samples', 'ci.mgit_id', 
			'ci.tube_id_lj', 'ci.tube_id_lc', 'ci.inoculation_date', 'tld.lj_result','t_service_log.tag','t_service_log.enroll_id AS enrollID','t_service_log.sample_id AS sampleID',
		  't_service_log.rec_flag')
                ->leftjoin('sample as m', 'm.id', '=', 't_service_log.sample_id')
                ->leftjoin('t_lj_detail as tld', 'tld.sample_id', '=', 't_service_log.sample_id')
                //->leftjoin('t_microscopy as s','s.sample_id','=','t_service_log.sample_id')
                ->leftjoin('t_microscopy as s', function ($join) {
                    $join->on('s.sample_id', '=', 't_service_log.sample_id')
                        ->where('s.status', 1);
                })
                ->leftjoin('t_culture_inoculation as ci', 'ci.sample_id', '=', 't_service_log.sample_id')
                ->where('t_service_log.id', $id)
                //->where('s.status',1)
                ->first();
            //dd($data['sample']);
            $data["dp"] = ["Ongoing", "POS", "NEG", "CONTA", "NTM"];
            return view('admin.lj.dashboard', compact('data'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return view('admin.layout.error', $error);  
        }
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


//        dd( $request->all(), $request->is_pos == 1 );

        $logdata = ServiceLog::find($id);
// dd($logdata);


        if (!empty($request->lj_main)) {

            if ($request->result == 'Ongoing') {

                $past_data_id = LJWeekLog::select('id')->where('sample_id', $logdata->sample_id)->where('enroll_id', $logdata->enroll_id)->where('status', 1)->first();
                if ($past_data_id) {
                    $past_data = LJWeekLog::find($past_data_id->id);
                    $past_data->status = 0;
                    $past_data->save();
                }

                $data = LJWeekLog::create([
                    'sample_id' => $logdata->sample_id,
                    'enroll_id' => $logdata->enroll_id,
                    'result' => $request->result,
                    'week' => $request->week,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id
                ]);
                $logdata->status = $logdata->status + 1;
                $logdata->week_log = $logdata->week_log + 1;


            } else {

                LJDetail::where('sample_id', $logdata->sample_id)->where('enroll_id', $logdata->enroll_id)->delete();

                $data = LJDetail::create([
                    'sample_id' => $logdata->sample_id,
                    'enroll_id' => $logdata->enroll_id,
                    'test_id' => $request->test_id,
                    'culture_smear' => $request->culture_smear,
                    'final_result' => $request->final_result,
                    'other_result' => $request->other_result,
                    'species' => $request->species,
                    'lj_result_date' => $request->lj_result_date,
                    'result_week' => $request->week,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                    'lj_result' => $request->result, // Save result in LJ Result
                ]);
                $logdata->released_dt = date('Y-m-d');
                $logdata->status = -1;

            }


            // dd($logdata);

            $logdata->save();
            // $week = $request->week+1;
            return redirect("/dashboardlj");

        }


        if ($request->is_pos == 1) {

            $count = LJDetail::where('sample_id', $logdata->sample_id)->where('enroll_id', $logdata->enroll_id)->count();
//             dd($count);

            if ($count > 0) {
                $ljdetail_data = LJDetail::select('id')->where('enroll_id', $logdata->enroll_id)->first();
                // dd($ljdetail_data->id);


                if (!empty($ljdetail_data->id)) {

                    if (!empty($request->idsubmit) && !empty($request->test_id)) {

                        LJDetail::where('id', $ljdetail_data->id)
                            ->update([
                                'test_id' => $request->test_id,
                                'lj_result' => $request->result, // Save result in LJ Result
                            ]);
                    }

                    if (!empty($request->smearsubmit) && !empty($request->culture_smear)) {
                        LJDetail::where('id', $ljdetail_data->id)
                            ->update([
                                'culture_smear' => $request->culture_smear,
                                'lj_result' => $request->result, // Save result in LJ Result
                            ]);

                    }


                    return redirect("/dashboardlj/" . $id . "");
                }


            } else {

                if (!empty($request->final_result)) {
                    $data = LJDetail::create([
                        'sample_id' => $logdata->sample_id,
                        'enroll_id' => $logdata->enroll_id,
                        'test_id' => $request->test_id,
                        'culture_smear' => $request->culture_smear,
                        'final_result' => $request->final_result,
                        'other_result' => $request->other_result,
                        'species' => $request->species,
                        'lj_result_date' => $request->lj_result_date,
                        'result_week' => $request->week,
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id,
                        'lj_result' => $request->result, // Save result in LJ Result
                    ]);

                } else {
//                     dd($request->all());

                    $data = LJDetail::create([
                        'sample_id' => $logdata->sample_id,
                        'enroll_id' => $logdata->enroll_id,
                        'test_id' => $request->test_id,
                        'culture_smear' => $request->culture_smear,
                        'other_result' => $request->other_result,
                        'species' => $request->species,
                        'lj_result_date' => $request->lj_result_date,
                        'result_week' => $request->week,
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id,
                        'lj_result' => $request->result, // Save result in LJ Result
                    ]);

                    return redirect("/dashboardlj/" . $id . "");

                }


                // $logdata->released_dt=date('Y-m-d');
                // $logdata->status = -1;


            }


        }


        return redirect("/dashboardlj");
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

    public function ljprint()
    {

        $data = [];
        $data['sample'] = ServiceLog::select('m.enroll_id','m.id as sample_id', 'm.receive_date as receive','m.test_reason as reason','is_accepted','s.result','t_service_log.sample_label as samples','t_service_log.enroll_label as enroll_label','t_service_log.service_id','t_service_log.id as log_id', 't_service_log.status','m.no_of_samples', 'ci.mgit_id','ci.tube_id_lj','ci.tube_id_lc','ci.inoculation_date')
            ->leftjoin('sample as m','m.id','=','t_service_log.sample_id')
            ->leftjoin('t_microscopy as s','s.sample_id','=','t_service_log.sample_id')
            ->leftjoin('t_culture_inoculation as ci','ci.sample_id','=','t_service_log.sample_id')
            ->where('s.status', 1)
            ->where('t_service_log.service_id',20)
            //->where('s.status',1)
            ->where('t_service_log.status',1)
            ->orderBy('enroll_id','desc')
            ->get();
        $data['week'] = 1;
        $data['weeks'] = ["--Select--","Week 1","Week 2","Week 3","Week 4","Week 5","Week 6","Week 7","Week 8"];
        return view('admin.lj.print',compact('data'));

    }
}
