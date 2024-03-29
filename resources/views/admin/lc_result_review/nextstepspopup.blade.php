<!-- Modal Dialog -->
<div class="modal fade" id="nextpopupDiv" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"></h4>
        <h4 class="modal-title">Liquid Culture Result Review</h4>
      </div>
	  <div class="alert alert-danger hide"><h4></h4></div>
      <div class="modal-body">
        <p></p>
        <form class="form-horizontal form-material" action="{{ url('/lc_result_review') }}" method="post"  id="nxtpopup" >
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" name="service_log_id" id="next_log_id" value="">
		  
		   <input type="hidden" name="enrollId" id="enrollId" value="">
		  <input type="hidden" name="tagId" id="tagId" value="">				
		  <input type="hidden" name="sampleID" id="sampleID" value="">
		  <input type="hidden" name="serviceId" id="serviceId" value="">				
		  <input type="hidden" name="rec_flag" id="recFlagId" value="">
		  <input type="hidden" name="result" id="rsltId" value="">
		  
          <div class="col hide">
                <label class="col-md-12">Number of samples : <span class="red">*</span></label>
                <div class="col-md-12">
                   <input type="text" name="no_sample" class="form-control form-control-line sampleId" value="" id="no_sample" required>
               </div>
            </div>
          <div class="row">

            <div class="col ">
                <label class="col-md-12">Sample ID : <span class="red">*</span></label>
                <div class="col-md-12">
                   <input type="text" name="sample_id" class="form-control form-control-line sampleId" value="" id="next_sample_id" required>
               </div>
            </div>
          </div>
          <div class="row">
              <div class="col">
                  <label class="col-md-12">Sample Sent for :<span class="red">*</span></label>
                  <div class="col-md-12">
                     <select name="service_id" id="service_id" class="form-control form-control-line test_reason" required>
                       <option value="">--Select--</option>
                       <option value="1">DNA Extraction LPA 1st line</option>
                       <option value="2">DNA Extraction LPA 2nd line</option>
                       <!---<option value="3">DNA Extraction LPA 1st line and LPA 2nd line</option>----->
                       <option value="4">LC- DST- Inoculation</option>
                      {{-- <option value="6">Do LC with standby sample</option> --}}
                      <option value="7">Result finalization</option>
                      <!-- <option value="5">MB for further review </option>-->



                     </select>
                 </div>
              </div>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-md" data-dismiss="modal">Cancel</button>
        <button type="button" class="pull-right btn btn-primary btn-md" id="nxtconfirm">Ok</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){

  $('#service_id').on('change', function (e) {
    var service = $("#service_id").val();
    var no_sample = $("#no_sample").val();

    if(service==6 && no_sample=='0'){
      alert("standby sample not available");
      $("#service_id").val('');
    }

    if( service== 1 || service==2 )
    {
      $.ajax({
								  type: "POST",
								  url: "{{url('check-for-sample-lc-review')}}",
								  data: {
									_token:"{{ csrf_token() }}",
									sample_id: $("#sampleID").val(),
									enroll_id: $("#enrollId").val(),
									tag_id: $("#tagId").val(),									
								  },
								  success: function(data){
									//console.log(data.result);

                    if( data.result == true )
                    {
                      $('div .alert').html('<h4>Data will not be added</h4>');
                      $('div .alert').show();
                      $('#nxtconfirm').prop('disabled', true);
                    }								
								  },
								  dataType: "json"
								});
      }

   });

  $('#nextpopupDiv').on('show.bs.modal', function (e) {

       // Pass form reference to modal for submission on yes/ok
       var form = $(e.relatedTarget).closest('form');
       $(this).find('.modal-footer #confirm').data('form', form);
   });

});
</script>
