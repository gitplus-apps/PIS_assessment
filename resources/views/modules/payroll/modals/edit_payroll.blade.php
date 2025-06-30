<div class="modal fade" id="edit-payroll-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white" id="exampleModalLabel">Update Payroll Item</h5>
                <a href="#"><span class="close text-white" data-dismiss="modal" aria-label="Close"
                        aria-hidden="false">×</span></a>
            </div>
            <div class="modal-body">
                <form id="edit-item-form">
                    @csrf
                    <input type="text" name="transid" id="edit-item-code" hidden required>
                    <div class="form-group">
                        <input type="text" name="desc" id="edit-item-desc"
                            placeholder="Enter payroll item description eg.SSNIT" class="form-control form-control-sm"
                            required>
                    </div>
                    <div class="form-group mt-2">
                        <label for="">Type</label>
                        <select type="text" id="edit-item-type" name="type" class="form-control select2" required>
                            <option value="">--Select--</option>
                            <option value="ALL">Earnings</option>
                            <option value="DUD">Deductions</option>
                            <option value="CON">Contributions</option>
                            <option value="PEN">Pension</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light btn-sm rounded" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm rounded" form="edit-item-form" type="submit" name="submit"> <i
                        class=""></i> Update</button>
            </div>
        </div>
    </div>
</div>
<script>
    var editPayrollItemForm = document.getElementById("edit-item-form")
    $(editPayrollItemForm).submit(function(e) {
        e.preventDefault();

        // After checking the required inputs are not empty and is valid the form is then submitted
        let formdata = new FormData(editPayrollItemForm)
        formdata.append("createuser", createuser);
        formdata.append("school_code", school_code);
        Swal.fire({
            title: 'Are you sure you want to update payroll item?',
            text: "Or click cancel to abort!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Submit'

        }).then((result) => {

            if (result.value) {
                Swal.fire({
                    text: "Update please wait...",
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });
                fetch(`${appUrl}/staff/update_payroll_item`, {
                    method: "POST",
                    body: formdata,
                    headers: {
                        'XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
                        'laravel_session': $('meta[name="laravel_session"]').attr(
                            'content'),
                        'Accept': 'application/json'
                    }
                }).then(function(res) {
                    return res.json()
                }).then(function(data) {
                    if (!data.ok) {
                        Swal.fire({
                            text: data.msg,
                            type: "error"
                        });
                        return;
                    }
                    Swal.fire({
                        text: "Payroll item updated  successfully",
                        type: "success"
                    });
                    $("#edit-payroll-modal").modal('hide');
                    $("select").val(null).trigger('change');
                    payrollItemTable.ajax.reload(null, false);
                    editPayrollItemForm.reset();

                }).catch(function(err) {
                    if (err) {
                        Swal.fire({
                            text: "Updating payroll failed"
                        });
                    }
                })
            }
        })
    });
</script>
